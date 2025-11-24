import React, { useEffect, useState, useRef, useCallback } from "react";
import {
  flexRender,
  getCoreRowModel,
  useReactTable,
  getFilteredRowModel,
} from "@tanstack/react-table";
import { useVirtualizer } from "@tanstack/react-virtual";
import { Button } from "./ui/button";
import { Copy, Check, Eye, Keyboard, X } from "lucide-react";
import { useToast } from "./ui/use-toast";
import { copyToClipboard } from "@/lib/utils";
import { FileViewer } from "./FileViewer";

const levelColors = {
  Notice: "bg-blue-500/20 text-blue-300",
  Warning: "bg-yellow-500/20 text-yellow-300",
  Error: "bg-red-500/20 text-red-300",
  Parse: "bg-red-500/20 text-red-300",
  Fatal: "bg-red-500/20 text-red-300",
};

// Pre-compiled regex patterns for maximum performance
const COMPILED_PATTERNS = [
  // Most common: Standard PHP error format
  /(?:PHP (?:Warning|Notice|Parse error|Fatal error|Deprecated):).*?\sin\s+(.+?\.php)\s+on\s+line\s+(\d+)/,
  // "thrown in" exception format - updated to handle paths with spaces
  /thrown in\s+(.+?\.php)\s+on\s+line\s+(\d+)/,
  // General "in file on line" format
  /in\s+(.+?\.php)\s+on\s+line\s+(\d+)/,
  // File(line) format - very common
  /(.+?\.php)\((\d+)\)/,
  // File:line format
  /(.+?\.php):(\d+)/,
  // Eval code pattern (less common, so last)
  /in\s+(.+?\.php)\((\d+)\)\s+:\s+eval\(\)'?d\s+code\s+on\s+line\s+(\d+)/,
];

const LINE_PATTERN = /(?:line|Line)\s+(\d+)/;
const FILE_PATTERN = /([^\s"']+\.php)/;
const extractionCache = new Map();
const CACHE_SIZE_LIMIT = 1000; // Prevent memory leaks

// Helper function to extract exact file path and line from PHP error messages
const extractErrorInfo = (message) => {
  if (!message) return null;

  if (extractionCache.has(message)) {
    return extractionCache.get(message);
  }

  let result = null;

  // Fast path: check if message even contains .php before regex processing
  if (!message.includes(".php")) {
    // Quick check for line number without .php file
    if (message.includes("line")) {
      const lineMatch = message.match(LINE_PATTERN);
      if (lineMatch) {
        result = { filePath: null, lineNumber: lineMatch[1] };
      }
    }
  } else {
    // Try patterns in order of likelihood for PHP errors
    for (let i = 0; i < COMPILED_PATTERNS.length; i++) {
      const match = message.match(COMPILED_PATTERNS[i]);
      if (match) {
        result = {
          filePath: match[1],
          lineNumber: match[2],
        };
        break;
      }
    }

    // Quick fallback if no main patterns matched
    if (!result) {
      const fileMatch = message.match(FILE_PATTERN);
      const lineMatch = message.match(LINE_PATTERN);

      if (fileMatch || lineMatch) {
        result = {
          filePath: fileMatch?.[1] || null,
          lineNumber: lineMatch?.[1] || null,
        };
      }
    }
  }

  // Cache management with size limit
  if (extractionCache.size >= CACHE_SIZE_LIMIT) {
    const firstKey = extractionCache.keys().next().value;
    extractionCache.delete(firstKey);
  }
  extractionCache.set(message, result);

  return result;
};

const extractLineNumber = (message) => {
  const errorInfo = extractErrorInfo(message);
  return errorInfo ? errorInfo.lineNumber : null;
};

// Update the MultilineMessage component for better light mode visibility
const MultilineMessage = ({
  message,
  maxLinesToShow = 3,
  rowId,
  isExpanded,
  onToggleExpanded,
}) => {
  const lines = message.split("\n");
  const isLongMessage = lines.length > maxLinesToShow;

  // Calculate what lines to show
  const visibleLines = isExpanded ? lines : lines.slice(0, maxLinesToShow);

  // Handle keyboard shortcuts
  useEffect(() => {
    const handleKeyPress = (e) => {
      // Only if this row is currently hovered
      const hoveredRow = document.querySelector("tr:hover[data-row-id]");
      if (hoveredRow && hoveredRow.dataset.rowId === rowId && isLongMessage) {
        // Toggle expanded state with 'e' key
        if (e.key.toLowerCase() === "e") {
          onToggleExpanded(rowId);
        }
      }
    };

    document.addEventListener("keydown", handleKeyPress);
    return () => document.removeEventListener("keydown", handleKeyPress);
  }, [rowId, isLongMessage, onToggleExpanded]);

  return (
    <div className="shrink whitespace-pre-wrap break-words py-2 font-mono text-sm text-foreground">
      {visibleLines.map((line, index) => (
        <div
          key={index}
          className="mx-[-8px] rounded px-[8px] py-0.5 transition-colors hover:bg-indigo-500/50"
        >
          {line}
        </div>
      ))}

      {isLongMessage && !isExpanded && (
        <div className="mx-[-8px] px-[8px] py-0.5 text-xs text-muted-foreground">
          ...
        </div>
      )}

      {isLongMessage && (
        <div className="mt-1">
          <Button
            variant="ghost"
            size="sm"
            className="h-6 rounded-sm !bg-[#1F2937] px-2 text-xs text-muted-foreground hover:bg-[#1F2937]/80"
            onClick={() => onToggleExpanded(rowId)}
            title={`${isExpanded ? "Collapse" : "Expand"} message (E)`}
          >
            {isExpanded ? "Show less" : `Show more`}
            <kbd className="ml-1 rounded-sm bg-muted px-1 text-xs">E</kbd>
          </Button>
        </div>
      )}
    </div>
  );
};

export function LogTable({ data, isLoading }) {
  const { toast } = useToast();
  const [fileViewerOpen, setFileViewerOpen] = useState(false);
  const [fileData, setFileData] = useState(null);
  const [expandedMessages, setExpandedMessages] = useState(new Set());
  const [showKeyboardHelp, setShowKeyboardHelp] = useState(false);
  const keyboardHelpRef = useRef(null);
  const tableContainerRef = useRef(null);

  // Function to toggle expanded state for a message
  const toggleMessageExpanded = useCallback((rowId) => {
    setExpandedMessages((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(rowId)) {
        newSet.delete(rowId);
      } else {
        newSet.add(rowId);
      }
      return newSet;
    });
  }, []);

  // Helper function to find the log entry by file path and line number
  const findLogEntryByFilePath = useCallback(
    (filePath, lineNumber) => {
      if (!data || !data.length) return null;

      const matchingEntry = data.find((entry) => {
        const errorInfo = extractErrorInfo(entry.message);
        if (!errorInfo) return false;

        return (
          errorInfo.filePath === filePath &&
          errorInfo.lineNumber === String(lineNumber)
        );
      });

      return matchingEntry ? matchingEntry.raw : null;
    },
    [data],
  );

  // Function to handle viewing file - memoized to prevent useEffect re-renders
  const handleViewFile = useCallback(
    async (filePath, lineNumber, logEntry) => {
      try {
        // Clean up the file path - ensure it's a properly formatted path for the API
        let cleanFilePath = filePath;

        // Normalize path separators (Windows to Unix style)
        cleanFilePath = cleanFilePath.replace(/\\/g, "/");

        // Extract wp-content paths if present (this is what our API expects)
        if (cleanFilePath.includes("/wp-content/")) {
          const wpContentMatch = cleanFilePath.match(/(wp-content\/.*)/);
          if (wpContentMatch && wpContentMatch[1]) {
            cleanFilePath = wpContentMatch[1];
          }
        }

        // Handle common WordPress core paths
        const wpCorePaths = ["/wp-admin/", "/wp-includes/"];

        for (const corePath of wpCorePaths) {
          if (cleanFilePath.includes(corePath)) {
            const coreMatch = cleanFilePath.match(
              new RegExp(`(${corePath.replace(/\//g, "\\/")}.*)`),
            );
            if (coreMatch && coreMatch[1]) {
              cleanFilePath = coreMatch[1];
              break;
            }
          }
        }

        // If we still have an absolute path that doesn't start with wp-content or other known paths,
        // try to determine if it's a plugin or theme file by the path structure
        if (
          cleanFilePath.startsWith("/") &&
          !cleanFilePath.startsWith("/wp-content/") &&
          !cleanFilePath.startsWith("/wp-admin/") &&
          !cleanFilePath.startsWith("/wp-includes/")
        ) {
          // Check if it's inside a plugins directory
          const pluginMatch = cleanFilePath.match(
            /\/plugins\/([^/]+\/.*\.php)/,
          );
          if (pluginMatch && pluginMatch[1]) {
            cleanFilePath = `wp-content/plugins/${pluginMatch[1]}`;
          }
          // Check if it's inside a themes directory
          else {
            const themeMatch = cleanFilePath.match(
              /\/themes\/([^/]+\/.*\.php)/,
            );
            if (themeMatch && themeMatch[1]) {
              cleanFilePath = `wp-content/themes/${themeMatch[1]}`;
            }
          }
        }

        // Last resort - if we still have an absolute path, just use the filename
        if (
          cleanFilePath.startsWith("/") &&
          !cleanFilePath.startsWith("/wp-")
        ) {
          const parts = cleanFilePath.split("/");
          const filename = parts[parts.length - 1];
          if (filename.endsWith(".php")) {
            cleanFilePath = filename;
          }
        }

        // Prepare the log entry if available
        const encodedLogEntry = logEntry ? encodeURIComponent(logEntry) : "";
        const logEntryParam = encodedLogEntry
          ? `&log_entry=${encodedLogEntry}`
          : "";

        const response = await fetch(
          `./api.php?action=get_file_content&file=${encodeURIComponent(
            cleanFilePath,
          )}&line=${lineNumber}${logEntryParam}`,
        );
        const data = await response.json();

        // Create a default fileData structure with fallbacks in case of missing properties
        const defaultFileData = {
          content: ["No content available"],
          file: cleanFilePath || "Unknown file",
          error_line: parseInt(lineNumber) || 1,
          start_line: 1,
          total_lines: 1,
          logEntry: logEntry || null,
        };

        if (!data || data.error) {
          const errorMessage =
            data && data.error ? data.error : "Failed to retrieve file content";

          toast({
            title: "Warning",
            description: errorMessage,
            variant: "destructive",
          });

          // Even with an error, we'll still open the file viewer with the data we have
          // The API should include default fallback data even in error cases
          const fileViewerData =
            data && data.data
              ? {
                  ...defaultFileData,
                  ...data.data,
                  logEntry:
                    data.data.logEntry ||
                    data.data.original_log_entry ||
                    logEntry ||
                    null,
                }
              : defaultFileData;

          setFileData(fileViewerData);
          setFileViewerOpen(true);
          return;
        }

        // Find the log entry that triggered this file view
        const apiData = data.data || data; // Handle both response formats
        const finalLogEntry =
          apiData.logEntry ||
          apiData.original_log_entry ||
          logEntry ||
          findLogEntryByFilePath(filePath, lineNumber);

        // Construct the file data with fallbacks for any missing properties
        const fileViewerData = {
          ...defaultFileData,
          ...apiData,
          content: Array.isArray(apiData.content)
            ? apiData.content
            : ["No content available"],
          file: apiData.file || cleanFilePath || "Unknown file",
          error_line: parseInt(apiData.error_line) || parseInt(lineNumber) || 1,
          start_line: apiData.start_line || 1,
          total_lines:
            apiData.total_lines ||
            (Array.isArray(apiData.content) ? apiData.content.length : 1),
          logEntry: finalLogEntry,
        };

        setFileData(fileViewerData);
        setFileViewerOpen(true);
      } catch (error) {
        toast({
          title: "Error",
          description: `Failed to fetch file contents: ${error.message}`,
          variant: "destructive",
        });

        // Create a fallback view when we encounter an error
        const fallbackData = {
          content: [
            `Error loading file: ${error.message}`,
            "",
            "Could not load the requested file.",
          ],
          file: filePath || "Unknown file",
          error_line: parseInt(lineNumber) || 1,
          start_line: 1,
          total_lines: 3,
          logEntry: logEntry || null,
        };

        setFileData(fallbackData);
        setFileViewerOpen(true);
      }
    },
    [findLogEntryByFilePath, toast],
  );

  // Define columns with the same structure but optimized for virtualization
  const columns = [
    {
      accessorKey: "number",
      header: "#",
      size: 45,
      maxSize: 45,
    },
    {
      accessorKey: "timestamp",
      header: "Timestamp",
      size: 200,
      maxSize: 200,
    },
    {
      accessorKey: "level",
      header: "Level",
      size: 90,
      maxSize: 90,
      cell: ({ row }) => {
        const level = row.original.level;
        return (
          <span
            className={`rounded px-2 py-0.5 text-xs font-medium ${
              levelColors[level] || levelColors.Notice
            }`}
          >
            {level}
          </span>
        );
      },
    },
    {
      accessorKey: "message",
      header: "Message",
      size: undefined,
      cell: ({ row }) => {
        const message = row.original.message;
        const lines = message.split("\n");
        const isMultiline = lines.length > 1;

        if (!isMultiline) {
          return (
            <div className="shrink whitespace-pre-wrap break-words py-2 font-mono text-sm text-foreground">
              {message}
            </div>
          );
        }

        return (
          <MultilineMessage
            message={message}
            maxLinesToShow={3}
            rowId={row.id}
            isExpanded={expandedMessages.has(row.id)}
            onToggleExpanded={toggleMessageExpanded}
          />
        );
      },
    },
    {
      accessorKey: "line",
      header: "Line",
      size: 55,
      maxSize: 55,
      cell: ({ row }) => {
        const lineNumber = extractLineNumber(row.original.message);
        return lineNumber ? (
          <span className="rounded bg-[#C7337E]/20 px-2 py-0.5 text-xs font-medium text-[#C7337E]">
            {lineNumber}
          </span>
        ) : null;
      },
    },
    {
      id: "actions",
      size: 160,
      maxSize: 160,
      cell: ({ row }) => {
        // eslint-disable-next-line react-hooks/rules-of-hooks
        const [isCopied, setIsCopied] = useState(false);

        const copyRow = async () => {
          const success = await copyToClipboard(row.original.raw);
          if (success) {
            setIsCopied(true);
            toast({
              description: "Row copied to clipboard",
              duration: 2000,
              icon: <Check className="size-4" />,
            });
            setTimeout(() => setIsCopied(false), 2000);
          } else {
            toast({
              description: "Failed to copy to clipboard",
              duration: 2000,
            });
          }
        };

        const message = row.original.message;
        const errorInfo = extractErrorInfo(message);
        const filePath = errorInfo ? errorInfo.filePath : null;
        const lineNumber = errorInfo ? errorInfo.lineNumber : null;

        const canViewFile = !!filePath && !!lineNumber;

        return (
          <div className="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
            <Button
              variant="ghost"
              size="sm"
              className="flex h-7 items-center gap-1 bg-card text-muted-foreground hover:bg-muted"
              onClick={copyRow}
              title="Copy row (X)"
            >
              {isCopied ? (
                <Check className="size-4" />
              ) : (
                <Copy className="size-4" />
              )}
              <kbd className="rounded-sm bg-muted px-1 text-xs">X</kbd>
            </Button>

            {canViewFile && (
              <Button
                variant="ghost"
                size="sm"
                className="flex h-7 items-center gap-1 bg-card text-muted-foreground hover:bg-muted"
                onClick={() =>
                  handleViewFile(filePath, lineNumber, row.original.raw)
                }
                title="View file (Q)"
              >
                <Eye className="size-4" />
                <kbd className="rounded-sm bg-muted px-1 text-xs">Q</kbd>
              </Button>
            )}
          </div>
        );
      },
    },
  ];

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
  });

  const { rows } = table.getRowModel();

  const rowVirtualizer = useVirtualizer({
    count: rows.length,
    getScrollElement: () => tableContainerRef.current,
    estimateSize: () => 60, // Estimated row height
    overscan: 10, // Render 10 extra rows above and below visible area
  });

  // Update keyboard shortcut listener
  useEffect(() => {
    const handleKeyPress = async (e) => {
      const hoveredRow = document.querySelector("tr:hover[data-row-id]");
      if (!hoveredRow) return;

      const rowId = hoveredRow.dataset.rowId;
      const row = rows.find((r) => r.id === rowId);
      if (!row) return;

      if (e.key.toLowerCase() === "x") {
        const copyButton = hoveredRow.querySelector("button");
        const success = await copyToClipboard(row.original.raw);
        if (success) {
          const icon = document.createElement("div");
          icon.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-[#C7337E]"><polyline points="20 6 9 17 4 12"></polyline></svg>';
          copyButton.replaceChild(icon.firstChild, copyButton.firstChild);

          toast({
            description: "Row copied to clipboard",
            duration: 2000,
            icon: <Check className="size-4" />,
          });

          setTimeout(() => {
            const copyIcon = document.createElement("div");
            copyIcon.innerHTML =
              '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
            copyButton.replaceChild(copyIcon.firstChild, copyButton.firstChild);
          }, 2000);
        } else {
          toast({
            description: "Failed to copy to clipboard",
            duration: 2000,
          });
        }
      }

      if (e.key.toLowerCase() === "q") {
        const message = row.original.message;
        const errorInfo = extractErrorInfo(message);
        const filePath = errorInfo ? errorInfo.filePath : null;
        const lineNumber = errorInfo ? errorInfo.lineNumber : null;

        if (filePath && lineNumber) {
          handleViewFile(filePath, lineNumber, row.original.raw);
        } else {
          console.log("Unable to view file - missing path or line number:", {
            level: row.original.level,
            message:
              message.substring(0, 100) + (message.length > 100 ? "..." : ""),
          });
        }
      }
    };

    document.addEventListener("keydown", handleKeyPress);
    return () => document.removeEventListener("keydown", handleKeyPress);
  }, [rows, rowVirtualizer, toast, handleViewFile]);

  // Close keyboard help when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        keyboardHelpRef.current &&
        !keyboardHelpRef.current.contains(event.target)
      ) {
        setShowKeyboardHelp(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  if (isLoading) {
    return (
      <div className="flex h-96 w-full items-center justify-center rounded-lg bg-card">
        <div className="text-muted-foreground">Loading logs...</div>
      </div>
    );
  }

  if (!data || data.length === 0) {
    return (
      <div className="flex h-96 w-full items-center justify-center rounded-lg bg-card">
        <div className="text-muted-foreground">No logs found</div>
      </div>
    );
  }

  return (
    <>
      <div className="overflow-hidden rounded-lg border border-border bg-card">
        <div className="overflow-x-auto">
          <table className="w-full table-fixed">
            <thead>
              {table.getHeaderGroups().map((headerGroup) => (
                <tr key={headerGroup.id} className="border-b border-border">
                  {headerGroup.headers.map((header) => (
                    <th
                      key={header.id}
                      style={{
                        width: header.column.columnDef.size
                          ? `${header.column.columnDef.size}px`
                          : "auto",
                        maxWidth: header.column.columnDef.maxSize
                          ? `${header.column.columnDef.maxSize}px`
                          : "none",
                      }}
                      className="truncate bg-card p-2 text-left text-xs font-medium text-muted-foreground"
                    >
                      {flexRender(
                        header.column.columnDef.header,
                        header.getContext(),
                      )}
                    </th>
                  ))}
                </tr>
              ))}
            </thead>
          </table>
        </div>

        <div
          ref={tableContainerRef}
          className="overflow-auto"
          style={{
            height: "calc(100vh - 200px)", // Fixed height for virtualization
          }}
        >
          <div
            style={{
              height: `${rowVirtualizer.getTotalSize()}px`,
              width: "100%",
              position: "relative",
            }}
          >
            {rowVirtualizer.getVirtualItems().map((virtualItem) => {
              const row = rows[virtualItem.index];
              return (
                <div
                  key={row.id}
                  data-index={virtualItem.index}
                  ref={(node) => rowVirtualizer.measureElement(node)}
                  style={{
                    position: "absolute",
                    top: 0,
                    left: 0,
                    width: "100%",
                    transform: `translateY(${virtualItem.start}px)`,
                  }}
                >
                  <table className="w-full table-fixed">
                    <tbody>
                      <tr
                        data-row-id={row.id}
                        className="group border-b border-border transition-colors hover:bg-muted"
                      >
                        {row.getVisibleCells().map((cell) => (
                          <td
                            key={cell.id}
                            style={{
                              width: cell.column.columnDef.size
                                ? `${cell.column.columnDef.size}px`
                                : "auto",
                              maxWidth: cell.column.columnDef.maxSize
                                ? `${cell.column.columnDef.maxSize}px`
                                : "none",
                            }}
                            className="p-2 text-sm text-foreground"
                          >
                            {flexRender(
                              cell.column.columnDef.cell,
                              cell.getContext(),
                            )}
                          </td>
                        ))}
                      </tr>
                    </tbody>
                  </table>
                </div>
              );
            })}
          </div>
        </div>

        {/* Updated Footer - Remove pagination, add scroll info */}
        <div className="flex items-center justify-between border-t border-border bg-card p-4">
          <div className="flex items-center gap-2">
            <div className="relative">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setShowKeyboardHelp(!showKeyboardHelp)}
                className="flex items-center gap-2 border-border text-muted-foreground hover:text-foreground"
                title="Keyboard shortcuts"
              >
                <Keyboard className="size-4" />
                <span className="hidden sm:inline">Shortcuts</span>
              </Button>

              {showKeyboardHelp && (
                <div
                  ref={keyboardHelpRef}
                  className="absolute bottom-full left-0 z-50 mb-2 w-64 rounded-md border border-border bg-card p-3 shadow-lg"
                >
                  <div className="mb-2 flex items-center justify-between border-b border-border pb-1">
                    <h3 className="text-sm font-medium">Keyboard Shortcuts</h3>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => setShowKeyboardHelp(false)}
                      className="size-6 p-0"
                    >
                      <X className="size-3.5" />
                    </Button>
                  </div>
                  <div className="space-y-1 text-xs">
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">
                        Copy log entry
                      </span>
                      <kbd className="rounded-sm bg-muted px-1.5 text-xs">
                        X
                      </kbd>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">View file</span>
                      <kbd className="rounded-sm bg-muted px-1.5 text-xs">
                        Q
                      </kbd>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">
                        Expand/collapse message
                      </span>
                      <kbd className="rounded-sm bg-muted px-1.5 text-xs">
                        E
                      </kbd>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">Navigate up</span>
                      <kbd className="rounded-sm bg-muted px-1.5 text-xs">
                        ↑
                      </kbd>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-muted-foreground">
                        Navigate down
                      </span>
                      <kbd className="rounded-sm bg-muted px-1.5 text-xs">
                        ↓
                      </kbd>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
      {fileData && (
        <FileViewer
          isOpen={fileViewerOpen}
          onClose={() => setFileViewerOpen(false)}
          fileData={fileData}
        />
      )}
    </>
  );
}
