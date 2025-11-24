import React, {
  useState,
  useEffect,
  useCallback,
  useRef,
  useMemo,
} from "react";
import { LogTable } from "./components/LogTable";
import { Button } from "./components/ui/button";
import { RefreshCw, Check, Filter, Clock, ListFilter } from "lucide-react";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "./components/ui/command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "./components/ui/popover";
import { useToast } from "./components/ui/use-toast";
import { copyToClipboard } from "@/lib/utils";
import { Toolbar } from "./components/Toolbar";
import { ModulesManager } from "./components/ModulesManager";
import { initCsrfToken, clearLogs } from "./services/api";
import Auth from "./components/Auth";

// Constants for filters
const LOG_LEVELS = ["Notice", "Warning", "Fatal", "Parse", "Error"];
const TIME_FILTERS = [
  { value: "all", label: "All time" },
  { value: "1h", label: "Last hour" },
  { value: "24h", label: "Last 24 hours" },
  { value: "7d", label: "Last 7 days" },
];
const SOURCE_FILTERS = [
  { value: "core", label: "WordPress Core", pattern: /wp-includes|wp-admin/ },
  { value: "plugins", label: "Plugins", pattern: /wp-content\/plugins/ },
  { value: "themes", label: "Themes", pattern: /wp-content\/themes/ },
  {
    value: "custom",
    label: "Custom Code",
    pattern: /wp-content\/(mu-plugins|custom)/,
  },
];

function parseLogs(logs) {
  if (!Array.isArray(logs)) {
    return [];
  }

  const parsedLogs = [];

  // Join all lines into a single string first
  const fullLog = logs.join("\n");

  // Split the log into entries based on timestamp pattern
  // This regex looks for lines that start with a timestamp pattern [DD-MMM-YYYY HH:MM:SS UTC]
  const logEntries = fullLog.split(
    /\n(?=\[\d{2}-[A-Za-z]{3}-\d{4}\s\d{2}:\d{2}:\d{2}\sUTC\])/,
  );
  // Process each log entry
  logEntries.forEach((entry) => {
    if (!entry.trim()) return; // Skip empty entries

    // Extract timestamp from the entry
    const timestampMatch = entry.match(
      /^\[(\d{2}-[A-Za-z]{3}-\d{4}\s\d{2}:\d{2}:\d{2}\sUTC)\]/,
    );
    const timestamp = timestampMatch ? timestampMatch[1] : "";

    // Determine log level
    let level = "Notice"; // Default level

    if (entry.includes("[User Notice]")) {
      level = "Notice";
    } else if (entry.includes("[User Warning]")) {
      level = "Warning";
    } else if (entry.includes("[User Error]")) {
      level = "Error";
    } else if (
      entry.includes("PHP Fatal error:") ||
      entry.includes("Stack trace:") ||
      entry.match(/^#\d+\s/) ||
      entry.includes("thrown in")
    ) {
      level = "Fatal";
    } else if (entry.includes("PHP Parse error:")) {
      level = "Parse";
    } else if (entry.includes("PHP Warning:")) {
      level = "Warning";
    } else if (entry.includes("PHP Notice:")) {
      level = "Notice";
    } else if (entry.includes("PHP Error:")) {
      level = "Error";
    } else if (entry.includes("Exception")) {
      level = "Fatal";
    }

    // Create the log entry object
    const logEntry = {
      number: parsedLogs.length + 1,
      timestamp: timestamp,
      level: level,
      message: entry
        .replace(/^\[\d{2}-[A-Za-z]{3}-\d{4}\s\d{2}:\d{2}:\d{2}\sUTC\]/, "")
        .trim(),
      raw: entry,
      lines: entry.split("\n"),
    };

    parsedLogs.push(logEntry);
  });

  // Reverse the array to show newest logs first
  const reversedLogs = parsedLogs.reverse();

  // Renumber the entries after reversing
  reversedLogs.forEach((log, index) => {
    log.number = index + 1;
  });

  return reversedLogs;
}

export default function App() {
  const [logs, setLogs] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [isDarkMode, setIsDarkMode] = useState(true);
  const [isAutoRefreshEnabled, setIsAutoRefreshEnabled] = useState(false);
  const [showFilters, setShowFilters] = useState(false);
  const [isExcludeFilter, setIsExcludeFilter] = useState(false);
  const [selectedLevels, setSelectedLevels] = useState([]);
  const [timeFilter, setTimeFilter] = useState("all");
  const [selectedSources, setSelectedSources] = useState([]);
  const [modulesManagerOpen, setModulesManagerOpen] = useState(false);
  const [levelPopoverOpen, setLevelPopoverOpen] = useState(false);
  const [timePopoverOpen, setTimePopoverOpen] = useState(false);
  const [sourcePopoverOpen, setSourcePopoverOpen] = useState(false);
  const [isInitializing, setIsInitializing] = useState(true);
  const [csrfError, setCsrfError] = useState(false);
  const [authStatus, setAuthStatus] = useState({
    requiresAuth: false,
    isAuthenticated: false,
    isChecking: true,
  });

  const searchInputRef = useRef(null);
  const levelFilterRef = useRef(null);
  const timeFilterRef = useRef(null);
  const sourceFilterRef = useRef(null);

  const { toast } = useToast();

  // Handler functions defined early
  const handleToggleAutoRefresh = useCallback(() => {
    setIsAutoRefreshEnabled((prev) => !prev);
  }, []);

  const handleOpenModulesManager = useCallback(() => {
    setModulesManagerOpen(true);
  }, []);

  const handleToggleTheme = useCallback(() => {
    setIsDarkMode((prev) => {
      const newValue = !prev;
      document.documentElement.classList.toggle("dark", newValue);
      localStorage.setItem("darkMode", newValue.toString());
      return newValue;
    });
  }, []);

  // Memoize filteredLogs
  const filteredLogs = useMemo(() => {
    return logs.filter((log) => {
      // Text search filter
      const searchLower = searchQuery.toLowerCase();
      const matchesSearch =
        log.message.toLowerCase().includes(searchLower) ||
        log.level.toLowerCase().includes(searchLower);

      if (isExcludeFilter ? matchesSearch : !matchesSearch) {
        return false;
      }

      // Level filter
      if (selectedLevels.length > 0 && !selectedLevels.includes(log.level)) {
        return false;
      }

      // Time filter
      if (timeFilter !== "all") {
        const logTime = new Date(log.timestamp);
        const now = new Date();
        const diff = now - logTime;

        switch (timeFilter) {
          case "1h":
            if (diff > 3600000) return false; // 1 hour in ms
            break;
          case "24h":
            if (diff > 86400000) return false; // 24 hours in ms
            break;
          case "7d":
            if (diff > 604800000) return false; // 7 days in ms
            break;
        }
      }

      // Source filter
      if (selectedSources.length > 0) {
        const matchesSource = selectedSources.some((source) => {
          const sourcePattern = SOURCE_FILTERS.find(
            (f) => f.value === source,
          )?.pattern;
          return sourcePattern?.test(log.message);
        });
        if (!matchesSource) return false;
      }

      return true;
    });
  }, [
    logs,
    searchQuery,
    isExcludeFilter,
    selectedLevels,
    timeFilter,
    selectedSources,
  ]);

  const handleCopyLogs = useCallback(async () => {
    if (!filteredLogs.length) return;

    try {
      const text = filteredLogs.map((log) => log.raw).join("\n");
      const success = await copyToClipboard(text);

      if (success) {
        toast({
          description: "Logs copied to clipboard",
          duration: 2000,
          icon: <Check className="size-4" />,
        });
      } else {
        toast({
          description: "Failed to copy logs to clipboard",
          duration: 2000,
        });
      }
    } catch (err) {
      toast({
        description: "Failed to copy logs to clipboard",
        duration: 2000,
      });
    }
  }, [filteredLogs, toast]);

  const handleDownloadLogs = useCallback(() => {
    if (!filteredLogs.length) return;

    const text = filteredLogs
      .map((log) => `[${log.timestamp}] ${log.level}: ${log.message}`)
      .join("\n");
    const blob = new Blob([text], { type: "text/plain" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `debug-logs-${new Date().toISOString().split("T")[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  }, [filteredLogs]);

  const fetchLogs = useCallback(async () => {
    try {
      const response = await fetch("./api.php?action=get_logs");

      if (!response.ok) {
        throw new Error(
          `Failed to fetch logs: ${response.status} ${response.statusText}`,
        );
      }

      const data = await response.json();

      if (data.error) {
        setError(data.error);
        setLogs([]);
      } else if (Array.isArray(data.logs)) {
        // Original format: { logs: [] }
        const parsedLogData = parseLogs(data.logs);
        setLogs(parsedLogData);
        setError(null);
      } else if (data.data && Array.isArray(data.data.logs)) {
        // New format: { data: { logs: [] } }
        const parsedLogData = parseLogs(data.data.logs);
        setLogs(parsedLogData);
        setError(null);
      } else {
        setError("Invalid logs data format received from server");
        setLogs([]);
      }
    } catch (error) {
      setError(error.message);
      setLogs([]);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const handleClearLogs = useCallback(async () => {
    try {
      setIsLoading(true);
      // Use the API service instead of direct fetch
      const success = await clearLogs();
      if (!success) {
        throw new Error("Failed to clear logs");
      }
      await fetchLogs();
    } catch (error) {
      setError(error.message);
    } finally {
      setIsLoading(false);
    }
  }, [fetchLogs, setError, setIsLoading]);

  // Check authentication status
  const checkAuthStatus = useCallback(async () => {
    try {
      const response = await fetch("./api.php?action=check_auth");
      if (!response.ok) {
        throw new Error("Failed to check authentication status");
      }

      const data = await response.json();
      if (data.success && data.data) {
        const isAuthenticated = data.data.authenticated;
        setAuthStatus({
          requiresAuth: data.data.requires_auth,
          isAuthenticated: isAuthenticated,
          isChecking: false,
        });

        // If we're authenticated, fetch logs right away
        if (isAuthenticated) {
          setTimeout(() => fetchLogs(), 100); // Small delay to ensure state is updated
        }
      } else {
        throw new Error("Invalid authentication status response");
      }
    } catch (error) {
      console.error("Authentication check failed:", error);
      // Assume no authentication required if check fails
      setAuthStatus({
        requiresAuth: false,
        isAuthenticated: true,
        isChecking: false,
      });
    }
  }, [fetchLogs]);

  useEffect(() => {
    async function initialize() {
      try {
        // First check authentication status
        await checkAuthStatus();

        // Then initialize CSRF protection
        const csrfInitialized = await initCsrfToken();
        if (!csrfInitialized) {
          setCsrfError(true);
        }
      } catch (error) {
        console.error("Initialization error:", error);
        setCsrfError(true);
      } finally {
        setIsInitializing(false);
      }
    }

    initialize();
  }, [checkAuthStatus]);

  useEffect(() => {
    if (authStatus.isAuthenticated) {
      fetchLogs();
    }
  }, [fetchLogs, authStatus.isAuthenticated]);

  useEffect(() => {
    let interval;
    if (isAutoRefreshEnabled) {
      interval = setInterval(fetchLogs, 5000);
    }
    return () => {
      if (interval) {
        clearInterval(interval);
      }
    };
  }, [isAutoRefreshEnabled, fetchLogs]);

  // Handle keyboard shortcuts
  useEffect(() => {
    const handleKeyPress = (e) => {
      if (e.target.tagName === "INPUT" && e.key !== "Escape") {
        return;
      }

      if (e.metaKey || e.ctrlKey) {
        return;
      }

      switch (e.key) {
        case "a":
          setIsAutoRefreshEnabled((prev) => !prev);
          break;
        case "p":
          setModulesManagerOpen(true);
          break;
        case "c":
          handleCopyLogs();
          break;
        case "d":
          handleDownloadLogs();
          break;
        case "s":
          handleToggleTheme();
          break;
        case "r":
          handleClearLogs();
          break;
        case "l":
          e.preventDefault();
          searchInputRef.current?.focus();
          break;
        case "f":
          e.preventDefault();
          setShowFilters((prev) => !prev);
          break;
        case "escape":
          if (document.activeElement === searchInputRef.current) {
            searchInputRef.current.blur();
            setSearchQuery("");
          }
          break;
        case "1":
          if (showFilters) {
            e.preventDefault();
            setLevelPopoverOpen((prev) => !prev);
          }
          break;
        case "2":
          if (showFilters) {
            e.preventDefault();
            setTimePopoverOpen((prev) => !prev);
          }
          break;
        case "3":
          if (showFilters) {
            e.preventDefault();
            setSourcePopoverOpen((prev) => !prev);
          }
          break;
        case "t":
        case "T":
          if (showFilters) {
            setSelectedLevels([]);
            setTimeFilter("all");
            setSelectedSources([]);
          }
          break;
      }
    };

    window.addEventListener("keydown", handleKeyPress);
    return () => window.removeEventListener("keydown", handleKeyPress);
  }, [
    showFilters,
    handleCopyLogs,
    handleDownloadLogs,
    handleToggleTheme,
    handleClearLogs,
    handleToggleAutoRefresh,
  ]);

  // Initialize dark mode on mount
  useEffect(() => {
    const savedDarkMode = localStorage.getItem("darkMode");
    const initialDarkMode = savedDarkMode === "false" ? false : true;

    setIsDarkMode(initialDarkMode);
    // Remove any existing class first, then add if needed
    document.documentElement.classList.remove("dark");
    if (initialDarkMode) {
      document.documentElement.classList.add("dark");
    }
  }, []);

  // Add a toggleFilters function
  const handleToggleFilters = useCallback(() => {
    setShowFilters((prev) => !prev);
  }, []);

  // Show loading state while initializing
  if (isInitializing) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="text-center">
          <div className="mx-auto size-12 animate-spin rounded-full border-y-2 border-primary"></div>
          <p className="mt-4 text-gray-600">Loading Debug Toolkit...</p>
        </div>
      </div>
    );
  }

  // Show error state if CSRF initialization failed
  if (csrfError) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="max-w-md rounded-lg border border-red-200 bg-red-50 p-6 text-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="mx-auto size-12 text-red-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <h2 className="mt-4 text-xl font-semibold text-red-700">
            Security Initialization Failed
          </h2>
          <p className="mt-2 text-gray-700">
            Unable to initialize security features. Please refresh the page or
            contact your administrator.
          </p>
          <button
            onClick={() => window.location.reload()}
            className="mt-4 rounded bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700"
          >
            Refresh Page
          </button>
        </div>
      </div>
    );
  }

  // Show authentication screen if authentication is required and user is not authenticated
  if (
    authStatus.requiresAuth &&
    !authStatus.isAuthenticated &&
    !authStatus.isChecking
  ) {
    return <Auth onAuthenticated={checkAuthStatus} />;
  }

  return (
    <div className={`flex min-h-screen flex-col ${isDarkMode ? "dark" : ""}`}>
      <Toolbar
        onClearLogs={handleClearLogs}
        onCopyLogs={handleCopyLogs}
        onDownloadLogs={handleDownloadLogs}
        onToggleTheme={handleToggleTheme}
        onToggleAutoRefresh={handleToggleAutoRefresh}
        onOpenModulesManager={handleOpenModulesManager}
        onToggleFilters={handleToggleFilters}
        isAutoRefreshEnabled={isAutoRefreshEnabled}
        isDarkMode={isDarkMode}
        isFiltersVisible={showFilters}
        isLoading={isLoading}
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        searchInputRef={searchInputRef}
        isExcludeFilter={isExcludeFilter}
        setIsExcludeFilter={setIsExcludeFilter}
        isModulesManagerOpen={modulesManagerOpen}
      />

      <div className="container mx-auto flex-1 p-4">
        {showFilters && (
          <div className="mb-4 rounded-lg border bg-card p-4 shadow-sm">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
              <div className="flex-1 space-y-4 sm:flex sm:items-center sm:gap-4 sm:space-y-0">
                {/* Level Filter */}
                <div className="flex-1" ref={levelFilterRef}>
                  <Popover
                    open={levelPopoverOpen}
                    onOpenChange={setLevelPopoverOpen}
                  >
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        size="sm"
                        className="w-full justify-start border-border bg-background text-foreground"
                      >
                        <ListFilter className="mr-2 size-4" />
                        <span className="flex-1 text-left">
                          {selectedLevels.length === 0 && "All log levels"}
                          {selectedLevels.length === 1 &&
                            `Level: ${selectedLevels[0]}`}
                          {selectedLevels.length > 1 &&
                            `${selectedLevels.length} levels selected`}
                        </span>
                        <kbd className="ml-1 hidden rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground sm:inline">
                          1
                        </kbd>
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent
                      className="w-[200px] border border-border bg-popover/95 p-1 shadow-lg backdrop-blur-sm"
                      align="start"
                    >
                      <Command className="overflow-hidden rounded-lg border">
                        <CommandInput
                          placeholder="Search levels..."
                          className="cursor-text"
                        />
                        <CommandEmpty>No level found.</CommandEmpty>
                        <CommandGroup>
                          {LOG_LEVELS.map((level) => (
                            <CommandItem
                              key={level}
                              onSelect={() => {
                                setSelectedLevels((prev) =>
                                  prev.includes(level)
                                    ? prev.filter((l) => l !== level)
                                    : [...prev, level],
                                );
                              }}
                              className="group flex items-center justify-between"
                            >
                              <div className="flex items-center">
                                <div
                                  className={`mr-2 flex size-4 items-center justify-center rounded-sm border transition-colors ${
                                    selectedLevels.includes(level)
                                      ? "border-primary bg-primary"
                                      : "border-border hover:border-primary/50"
                                  }`}
                                >
                                  {selectedLevels.includes(level) && (
                                    <Check className="size-3 text-primary-foreground" />
                                  )}
                                </div>
                                {level}
                              </div>
                              <div className="rounded bg-muted px-1.5 py-0.5 text-xs opacity-0 transition-opacity group-hover:opacity-100 data-[state=selected]:opacity-100">
                                ↵
                              </div>
                            </CommandItem>
                          ))}
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>

                {/* Time Filter */}
                <div className="flex-1" ref={timeFilterRef}>
                  <Popover
                    open={timePopoverOpen}
                    onOpenChange={setTimePopoverOpen}
                  >
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        size="sm"
                        className="w-full justify-start border-border bg-background text-foreground"
                      >
                        <Clock className="mr-2 size-4" />
                        <span className="flex-1 text-left">
                          {TIME_FILTERS.find((t) => t.value === timeFilter)
                            ?.label || "Select time range"}
                        </span>
                        <kbd className="ml-1 hidden rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground sm:inline">
                          2
                        </kbd>
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent
                      className="w-[200px] border border-border bg-popover/95 p-1 shadow-lg backdrop-blur-sm"
                      align="start"
                    >
                      <Command className="overflow-hidden rounded-lg border">
                        <CommandInput
                          placeholder="Search time range..."
                          className="cursor-text"
                        />
                        <CommandEmpty>No time range found.</CommandEmpty>
                        <CommandGroup>
                          {TIME_FILTERS.map(({ value, label }) => (
                            <CommandItem
                              key={value}
                              onSelect={() => setTimeFilter(value)}
                              className="group flex items-center justify-between"
                            >
                              <div className="flex items-center">
                                <div
                                  className={`mr-2 flex size-4 items-center justify-center rounded-sm border transition-colors ${
                                    timeFilter === value
                                      ? "border-primary bg-primary"
                                      : "border-border hover:border-primary/50"
                                  }`}
                                >
                                  {timeFilter === value && (
                                    <Check className="size-3 text-primary-foreground" />
                                  )}
                                </div>
                                {label}
                              </div>
                              <div className="rounded bg-muted px-1.5 py-0.5 text-xs opacity-0 transition-opacity group-hover:opacity-100 data-[state=selected]:opacity-100">
                                ↵
                              </div>
                            </CommandItem>
                          ))}
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>

                {/* Source Filter */}
                <div className="flex-1" ref={sourceFilterRef}>
                  <Popover
                    open={sourcePopoverOpen}
                    onOpenChange={setSourcePopoverOpen}
                  >
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        size="sm"
                        className="w-full justify-start border-border bg-background text-foreground"
                      >
                        <Filter className="mr-2 size-4" />
                        <span className="flex-1 text-left">
                          {selectedSources.length === 0 && "All sources"}
                          {selectedSources.length === 1 &&
                            SOURCE_FILTERS.find(
                              (s) => s.value === selectedSources[0],
                            )?.label}
                          {selectedSources.length > 1 &&
                            `${selectedSources.length} sources selected`}
                        </span>
                        <kbd className="ml-1 hidden rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground sm:inline">
                          3
                        </kbd>
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent
                      className="w-[200px] border border-border bg-popover/95 p-1 shadow-lg backdrop-blur-sm"
                      align="start"
                    >
                      <Command className="overflow-hidden rounded-lg border">
                        <CommandInput
                          placeholder="Search sources..."
                          className="cursor-text"
                        />
                        <CommandEmpty>No source found.</CommandEmpty>
                        <CommandGroup>
                          {SOURCE_FILTERS.map(({ value, label }) => (
                            <CommandItem
                              key={value}
                              onSelect={() => {
                                setSelectedSources((prev) =>
                                  prev.includes(value)
                                    ? prev.filter((s) => s !== value)
                                    : [...prev, value],
                                );
                              }}
                              className="group flex items-center justify-between"
                            >
                              <div className="flex items-center">
                                <div
                                  className={`mr-2 flex size-4 items-center justify-center rounded-sm border transition-colors ${
                                    selectedSources.includes(value)
                                      ? "border-primary bg-primary"
                                      : "border-border hover:border-primary/50"
                                  }`}
                                >
                                  {selectedSources.includes(value) && (
                                    <Check className="size-3 text-primary-foreground" />
                                  )}
                                </div>
                                {label}
                              </div>
                              <div className="rounded bg-muted px-1.5 py-0.5 text-xs opacity-0 transition-opacity group-hover:opacity-100 data-[state=selected]:opacity-100">
                                ↵
                              </div>
                            </CommandItem>
                          ))}
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

              {/* Reset Button */}
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setSelectedLevels([]);
                  setTimeFilter("all");
                  setSelectedSources([]);
                }}
                className="flex items-center gap-2 whitespace-nowrap border-border bg-background text-muted-foreground hover:bg-background/80"
              >
                <RefreshCw className="size-4" />
                <span>Reset Filters</span>
                <kbd className="ml-1 rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                  T
                </kbd>
              </Button>
            </div>
          </div>
        )}

        {error && (
          <div className="mb-4 rounded-lg border border-destructive bg-destructive/20 px-4 py-3 text-destructive-foreground">
            {error}
          </div>
        )}

        <LogTable data={filteredLogs} isLoading={isLoading} />
      </div>

      <ModulesManager
        isOpen={modulesManagerOpen}
        onClose={() => setModulesManagerOpen(false)}
      />
    </div>
  );
}
