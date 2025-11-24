import React, { useEffect, useRef, useState } from "react";
import { Sheet, SheetContent, SheetHeader, SheetTitle } from "./ui/sheet";
import { ChevronDown, ChevronUp, FileText } from "lucide-react";

export function FileViewer({ isOpen, onClose, fileData }) {
  const errorLineRef = useRef(null);
  const contentRef = useRef(null);
  const [sheetReady, setSheetReady] = useState(false);
  const [showFullLogEntry, setShowFullLogEntry] = useState(false);
  const [isDarkMode, setIsDarkMode] = useState(
    document.documentElement.classList.contains("dark"),
  );

  // Safely access fileData properties with fallback values
  const safeData = {
    file: fileData.file || "Unknown file",
    error_line: fileData.error_line || 1,
    start_line: fileData.start_line || 1,
    content: Array.isArray(fileData.content)
      ? fileData.content
      : ["No content available"],
    total_lines: fileData.total_lines || 1,
    logEntry: fileData.logEntry || fileData.original_log_entry || null,
  };

  // Listen for theme changes
  useEffect(() => {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === "class") {
          setIsDarkMode(document.documentElement.classList.contains("dark"));
        }
      });
    });

    observer.observe(document.documentElement, { attributes: true });

    return () => observer.disconnect();
  }, []);

  // Determine the language based on file extension
  const getLanguage = (filename) => {
    if (!filename) return "php"; // Default to PHP if no filename

    const extension = filename.split(".").pop().toLowerCase();
    const extensionMap = {
      php: "php",
      js: "javascript",
      jsx: "jsx",
      ts: "typescript",
      tsx: "tsx",
      html: "html",
      css: "css",
      json: "json",
      md: "markdown",
    };
    return extensionMap[extension] || "php"; // Default to PHP for WordPress
  };

  // Function to scroll to error line - this works perfectly in the button
  const scrollToErrorLine = () => {
    if (errorLineRef.current && contentRef.current) {
      const errorLineTop = errorLineRef.current.offsetTop;
      const containerHeight = contentRef.current.clientHeight;
      const scrollTo = Math.max(0, errorLineTop - containerHeight / 2);

      contentRef.current.scrollTop = scrollTo;
    }
  };

  // Mark sheet as ready after it opens
  useEffect(() => {
    if (isOpen) {
      // Reset sheet ready state when sheet opens
      setSheetReady(false);
      setShowFullLogEntry(false);

      // Mark sheet as ready after a delay
      const timer = setTimeout(() => {
        setSheetReady(true);
      }, 100);

      return () => clearTimeout(timer);
    }
  }, [isOpen]);

  // Execute scroll when sheet is ready
  useEffect(() => {
    if (sheetReady && isOpen) {
      // Try scrolling at different intervals to ensure it works
      const timers = [50, 150, 300, 500].map((delay) =>
        setTimeout(() => {
          scrollToErrorLine();
        }, delay),
      );

      return () => timers.forEach(clearTimeout);
    }
  }, [sheetReady, isOpen]);

  // Add keyboard shortcut for jumping to error line
  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (e) => {
      // Jump to error line when 'j' is pressed
      if (e.key.toLowerCase() === "j") {
        scrollToErrorLine();
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [isOpen]);

  // Extract error message from file path
  const getErrorDetails = () => {
    const fileName = safeData.file.split("/").pop() || "unknown-file";
    const errorLine = safeData.error_line;
    const errorLineIndex = errorLine - safeData.start_line;
    const errorContent =
      errorLineIndex >= 0 && errorLineIndex < safeData.content.length
        ? safeData.content[errorLineIndex]
        : "";

    // Get the log entry if available
    const logEntry = safeData.logEntry;

    return {
      fileName,
      errorContent,
      logEntry,
    };
  };

  const { fileName, errorContent, logEntry } = getErrorDetails();

  // Toggle between showing error line and full log entry
  const toggleLogEntryView = () => {
    setShowFullLogEntry(!showFullLogEntry);
  };

  // Function to render the log entry
  const renderLogEntry = () => {
    // If showing full log entry, return the complete log
    if (showFullLogEntry || !errorContent) {
      return (
        <div className="whitespace-pre-wrap break-words">
          {logEntry
            .trim()
            .split("\n")
            .map((line, index) => (
              <div
                key={index}
                className="mx-[-8px] rounded px-[8px] py-0.5 transition-colors hover:bg-primary/20"
              >
                {line}
              </div>
            ))}
        </div>
      );
    }

    // Otherwise just show the error line
    return (
      <div className="whitespace-pre-wrap break-words">
        {errorContent || "No error content available"}
      </div>
    );
  };

  // Check if we have a log entry to toggle
  const hasLogEntry = !!logEntry;

  // Get the file language for syntax highlighting
  const language = getLanguage(fileName);

  // Simple but effective PHP syntax highlighting without external dependencies
  const applySyntaxHighlighting = (code) => {
    // If we're not showing PHP, just return the original text
    if (language !== "php") {
      return <span className="text-foreground">{code}</span>;
    }

    // List of PHP keywords to highlight
    const keywords = [
      "if",
      "else",
      "elseif",
      "while",
      "do",
      "for",
      "foreach",
      "break",
      "continue",
      "switch",
      "case",
      "default",
      "function",
      "return",
      "try",
      "catch",
      "finally",
      "throw",
      "class",
      "extends",
      "implements",
      "interface",
      "abstract",
      "public",
      "private",
      "protected",
      "static",
      "final",
      "const",
      "include",
      "require",
      "include_once",
      "require_once",
      "namespace",
      "use",
      "as",
      "new",
      "true",
      "false",
      "null",
      "echo",
      "print",
    ];

    // 1. Check for comments
    if (code.match(/^\s*(\/\/|#)/)) {
      // This is a comment line
      return <span className="php-comment">{code}</span>;
    }

    // 2. Check for strings, variables, keywords, functions
    let output = [];

    // Find PHP strings
    const stringRegex = /'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*"/g;
    let stringMatch;
    const strings = [];

    while ((stringMatch = stringRegex.exec(code)) !== null) {
      strings.push({
        index: stringMatch.index,
        value: stringMatch[0],
        length: stringMatch[0].length,
      });
    }

    // Find PHP variables
    const varRegex = /\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/g;
    let varMatch;
    const variables = [];

    while ((varMatch = varRegex.exec(code)) !== null) {
      variables.push({
        index: varMatch.index,
        value: varMatch[0],
        length: varMatch[0].length,
      });
    }

    // Find PHP function calls
    const functionRegex =
      /\b[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?=\s*\()/g;
    let functionMatch;
    const functions = [];

    while ((functionMatch = functionRegex.exec(code)) !== null) {
      functions.push({
        index: functionMatch.index,
        value: functionMatch[0],
        length: functionMatch[0].length,
      });
    }

    // Find PHP keywords
    const keywordRegex = new RegExp("\\b(" + keywords.join("|") + ")\\b", "g");
    let keywordMatch;
    const keywordsFound = [];

    while ((keywordMatch = keywordRegex.exec(code)) !== null) {
      keywordsFound.push({
        index: keywordMatch.index,
        value: keywordMatch[0],
        length: keywordMatch[0].length,
      });
    }

    // Combine all tokens and sort by index
    const allTokens = [
      ...strings.map((t) => ({ ...t, type: "string" })),
      ...variables.map((t) => ({ ...t, type: "variable" })),
      ...functions.map((t) => ({ ...t, type: "function" })),
      ...keywordsFound.map((t) => ({ ...t, type: "keyword" })),
    ].sort((a, b) => a.index - b.index);

    // Build the output by walking through the tokens
    let currentPosition = 0;

    // Handle overlapping tokens
    const usedRanges = [];
    const isRangeUsed = (start, end) => {
      return usedRanges.some(
        (range) =>
          (start >= range.start && start < range.end) ||
          (end > range.start && end <= range.end) ||
          (start <= range.start && end >= range.end),
      );
    };

    for (const token of allTokens) {
      const { index, value, length, type } = token;
      const tokenEnd = index + length;

      // Skip if this range overlaps with a previously used token
      if (isRangeUsed(index, tokenEnd)) {
        continue;
      }

      // Add plain text before this token
      if (index > currentPosition) {
        output.push(
          <span key={`text-${currentPosition}`}>
            {code.slice(currentPosition, index)}
          </span>,
        );
      }

      // Add the token with appropriate styling
      let tokenClassName = "";
      switch (type) {
        case "string":
          tokenClassName = "php-string";
          break;
        case "variable":
          tokenClassName = "php-variable";
          break;
        case "function":
          tokenClassName = "php-function";
          break;
        case "keyword":
          tokenClassName = "php-keyword";
          break;
      }

      output.push(
        <span key={`${type}-${index}`} className={tokenClassName}>
          {value}
        </span>,
      );

      // Mark this range as used
      usedRanges.push({ start: index, end: tokenEnd });

      // Update current position
      currentPosition = tokenEnd;
    }

    // Add any remaining text
    if (currentPosition < code.length) {
      output.push(<span key={`text-end`}>{code.slice(currentPosition)}</span>);
    }

    return <>{output}</>;
  };

  return (
    <Sheet open={isOpen} onOpenChange={onClose}>
      <SheetContent className="w-5/6 overflow-hidden rounded-l-xl border-border bg-card p-0 shadow-xl sm:max-w-none">
        <div className="flex h-full">
          {/* Left Panel - Error Details */}
          <div className="flex w-1/3 flex-col overflow-y-auto border-r border-border bg-card p-3">
            <SheetHeader className="mb-4 pb-4">
              <SheetTitle className="flex items-center text-xl font-bold text-foreground">
                <span className="mr-2 text-primary">•</span>Error Details
              </SheetTitle>
            </SheetHeader>

            <div className="flex-1 space-y-5">
              <div className="rounded-lg border border-border bg-muted/70 p-4 shadow-sm">
                <h3 className="mb-2 text-sm font-semibold text-primary">
                  File
                </h3>
                <p className="overflow-hidden text-ellipsis rounded-md font-mono text-sm text-foreground">
                  {safeData.file}
                </p>
              </div>

              <div className="rounded-lg border border-border bg-muted/70 p-4 shadow-sm">
                <h3 className="mb-2 text-sm font-semibold text-primary">
                  Line
                </h3>
                <p className="overflow-hidden text-ellipsis rounded-md font-mono text-sm text-foreground">
                  {safeData.error_line}
                </p>
              </div>

              <div className="rounded-lg border border-border bg-muted/70 p-4 shadow-sm">
                <h3 className="mb-2 text-sm font-semibold text-primary">
                  Error Content
                </h3>

                {/* Error Content Toggle Button */}
                {hasLogEntry && errorContent && (
                  <button
                    onClick={toggleLogEntryView}
                    className={`mb-3 flex w-full items-center justify-between rounded-md px-4 py-2 text-sm transition-colors ${
                      showFullLogEntry
                        ? "border border-primary/30 bg-primary/20 text-primary hover:bg-primary/30"
                        : "border border-border bg-muted text-foreground hover:bg-muted/70"
                    }`}
                  >
                    <span>
                      {showFullLogEntry
                        ? "Show Error Line Only"
                        : "Show Full Error Entry"}
                    </span>
                    {showFullLogEntry ? (
                      <ChevronUp className="ml-2 size-4" />
                    ) : (
                      <ChevronDown className="ml-2 size-4" />
                    )}
                  </button>
                )}

                {/* Error Content Display */}
                <div
                  className={`max-h-[300px] overflow-y-auto rounded-md border p-3 font-mono text-sm ${
                    showFullLogEntry
                      ? "border-primary/20 bg-primary/10 text-foreground"
                      : "border-destructive/20 bg-destructive/10 text-foreground"
                  }`}
                >
                  {renderLogEntry()}
                </div>
              </div>

              {errorContent && (
                <div className="mt-auto pt-4">
                  <button
                    onClick={scrollToErrorLine}
                    className="flex w-full items-center justify-center gap-2 rounded-md bg-primary px-4 py-3 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                  >
                    <span>Jump to Error Line</span>
                    <kbd className="rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">
                      J
                    </kbd>
                  </button>
                </div>
              )}
            </div>
          </div>

          {/* Right Panel - File Content */}
          <div className="flex h-full w-2/3 flex-col">
            <div className="!rounded-bl-[10px] border-b border-border bg-muted p-4">
              <h2 className="flex items-center text-lg font-semibold text-foreground">
                <FileText className="mr-[15px] size-5 text-primary" />
                {safeData.file}
              </h2>
              <div className="mt-1 text-xs text-muted-foreground">
                {safeData.total_lines} lines | Error on line{" "}
                {safeData.error_line}
              </div>
            </div>

            <div
              ref={contentRef}
              className="flex-1 overflow-y-auto bg-card py-2"
            >
              <div className="code-highlight-custom">
                {/* eslint-disable-next-line react/no-unknown-property */}
                <style jsx>{`
                  .code-highlight-custom {
                    font-family:
                      ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
                      monospace;
                  }
                  .code-highlight-custom .php-keyword {
                    color: ${isDarkMode ? "#569cd6" : "#00009f"};
                  }
                  .code-highlight-custom .php-function {
                    color: ${isDarkMode ? "#dcdcaa" : "#6f42c1"};
                  }
                  .code-highlight-custom .php-variable {
                    color: ${isDarkMode ? "#9cdcfe" : "#36acaa"};
                  }
                  .code-highlight-custom .php-string {
                    color: ${isDarkMode ? "#ce9178" : "#a31515"};
                  }
                  .code-highlight-custom .php-comment {
                    color: ${isDarkMode ? "#6a9955" : "#a0a1a7"};
                    font-style: italic;
                  }
                  .code-highlight-custom .line {
                    display: flex;
                    padding: 0.375rem 1rem;
                    transition-property: color, background-color, border-color;
                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                    transition-duration: 150ms;
                  }
                  .code-highlight-custom .line-number {
                    display: inline-block;
                    width: 3rem;
                    text-align: right;
                    padding-right: 1rem;
                    user-select: none;
                    color: ${isDarkMode ? "#565656" : "#a0a1a7"};
                  }
                  .code-highlight-custom .line-content {
                    flex: 1;
                    color: ${isDarkMode ? "#e6e6e6" : "#383a42"};
                    margin: 0;
                    font-family: inherit;
                    background: none;
                    padding: 0;
                    font-size: inherit;
                    display: flex;
                    align-items: center;
                  }
                  .code-highlight-custom .error-line {
                    background-color: ${isDarkMode
                      ? "rgba(239, 68, 68, 0.2)"
                      : "rgba(252, 165, 165, 0.2)"};
                    border-left: 2px solid
                      ${isDarkMode ? "rgb(239, 68, 68)" : "rgb(248, 113, 113)"};
                  }
                  .code-highlight-custom .line:hover:not(.error-line) {
                    background-color: ${isDarkMode
                      ? "rgba(55, 65, 81, 0.7)"
                      : "rgba(243, 244, 246, 0.7)"};
                    border-left: 2px solid transparent;
                  }
                `}</style>
                <div>
                  {safeData.content.map((line, index) => {
                    const lineNumber = safeData.start_line + index;
                    const isErrorLine = lineNumber === safeData.error_line;

                    return (
                      <div
                        key={index}
                        ref={isErrorLine ? errorLineRef : null}
                        id={
                          isErrorLine
                            ? `error-line-${safeData.error_line}`
                            : undefined
                        }
                        className={`line ${isErrorLine ? "error-line" : ""}`}
                      >
                        <span className="line-number">{lineNumber}:</span>
                        <pre className="line-content">
                          {applySyntaxHighlighting(line)}
                        </pre>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>
        </div>
      </SheetContent>
    </Sheet>
  );
}
