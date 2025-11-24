import React from "react";
import { Button } from "./ui/button";
import { Toggle } from "./ui/toggle";
import { Input } from "./ui/input";
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuSeparator,
} from "./ui/dropdown-menu";
import {
  Trash2,
  Copy,
  Download,
  Sun,
  Moon,
  RefreshCw,
  Power,
  Filter,
  Keyboard,
  X,
} from "lucide-react";

export function Toolbar(props) {
  const {
    onClearLogs,
    onCopyLogs,
    onDownloadLogs,
    onToggleTheme,
    onToggleAutoRefresh,
    onOpenModulesManager,
    onToggleFilters,
    isAutoRefreshEnabled,
    isDarkMode,
    isFiltersVisible,
    searchQuery,
    setSearchQuery,
    searchInputRef,
    isExcludeFilter,
    setIsExcludeFilter,
    isModulesManagerOpen,
  } = props;

  return (
    <div className="border-b bg-card">
      {/* Main toolbar */}
      <div className="container mx-auto flex flex-wrap items-center justify-between gap-3 px-4 py-2">
        <div className="flex max-w-xl flex-1 items-center gap-4">
          {/* Logo */}
          <div className="flex shrink-0 items-center">
            <img
              src={"./assets/images/logo.png"}
              alt="WP Debug Toolkit Logo"
              className="size-11"
            />
          </div>

          {/* Search Bar */}
          <div className="relative w-full">
            <Input
              type="text"
              placeholder="Search logs... (Press 'L' to focus)"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              ref={searchInputRef}
              className="h-10 w-full rounded-[10px] border-border bg-background pr-24"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                title="Clear search"
              >
                <X className="size-4" />
              </button>
            )}
            <div className="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1">
              <button
                onClick={() => setIsExcludeFilter(!isExcludeFilter)}
                className={`rounded-full border px-2 py-0.5 text-xs ${
                  isExcludeFilter
                    ? "border-destructive/50 bg-destructive/20 text-destructive hover:bg-destructive/30"
                    : "border-primary/30 bg-primary/20 text-primary hover:bg-primary/30"
                } transition-colors`}
                title={
                  isExcludeFilter
                    ? "Exclude matching logs"
                    : "Include matching logs"
                }
              >
                {isExcludeFilter ? "exclude" : "include"}
              </button>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2">
          {/* Primary Actions Group */}
          <div className="flex items-center rounded-md border bg-background/50">
            <Toggle
              pressed={isAutoRefreshEnabled}
              onPressedChange={onToggleAutoRefresh}
              aria-label="Toggle auto-refresh"
              className={`group relative z-10 flex items-center gap-2 rounded-none rounded-l-md border-r px-3 py-2 transition-all duration-300 ${
                isAutoRefreshEnabled
                  ? "bg-primary text-primary-foreground"
                  : "hover:bg-muted/70"
              }`}
              title="Auto-refresh logs every 5 seconds"
              data-active={isAutoRefreshEnabled}
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[90px]">
                <div className="flex items-center">
                  <RefreshCw className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[80px] group-hover:opacity-100">
                    Auto Refresh
                  </span>
                </div>
                <kbd
                  className={`ml-1.5 rounded px-1.5 py-0.5 text-[10px] opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50 ${
                    isAutoRefreshEnabled
                      ? "bg-white/20 text-white"
                      : "bg-muted text-muted-foreground"
                  }`}
                >
                  A
                </kbd>
              </div>
            </Toggle>

            <Button
              variant="ghost"
              size="sm"
              onClick={onToggleFilters}
              className={`group relative z-20 h-full rounded-none border-r px-3 py-2 transition-all duration-300 hover:bg-muted/70 ${
                isFiltersVisible ? "bg-primary text-primary-foreground" : ""
              }`}
              title="Show/hide filters panel"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[60px]">
                <div className="flex items-center">
                  <Filter className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[50px] group-hover:opacity-100">
                    Filters
                  </span>
                </div>
                <kbd
                  className={`ml-1.5 rounded px-1.5 py-0.5 text-[10px] opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50 ${
                    isFiltersVisible
                      ? "bg-white/20 text-white"
                      : "bg-muted text-muted-foreground"
                  }`}
                >
                  F
                </kbd>
              </div>
            </Button>

            <Button
              variant="ghost"
              size="sm"
              onClick={onOpenModulesManager}
              className={`group relative z-30 h-full rounded-none rounded-r-md px-3 py-2 transition-all duration-300 hover:bg-muted/70 ${
                isModulesManagerOpen ? "bg-primary text-primary-foreground" : ""
              }`}
              title="Manage WordPress plugins and themes"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[110px]">
                <div className="flex items-center">
                  <Power className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[100px] group-hover:opacity-100">
                    Crash Recovery
                  </span>
                </div>
                <kbd
                  className={`ml-1.5 rounded px-1.5 py-0.5 text-[10px] opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50 ${
                    isModulesManagerOpen
                      ? "bg-white/20 text-white"
                      : "bg-muted text-muted-foreground"
                  }`}
                >
                  P
                </kbd>
              </div>
            </Button>
          </div>

          {/* Secondary Actions */}
          <div className="flex items-center rounded-md border bg-background/50">
            <Button
              variant="ghost"
              size="sm"
              onClick={onCopyLogs}
              className="group relative z-10 h-full rounded-none rounded-l-md border-r px-3 py-2 transition-all duration-300 hover:bg-muted/70"
              title="Copy logs to clipboard"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[50px]">
                <div className="flex items-center">
                  <Copy className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[40px] group-hover:opacity-100">
                    Copy
                  </span>
                </div>
                <kbd className="ml-1.5 rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50">
                  C
                </kbd>
              </div>
            </Button>

            <Button
              variant="ghost"
              size="sm"
              onClick={onDownloadLogs}
              className="group relative z-20 h-full rounded-none border-r px-3 py-2 transition-all duration-300 hover:bg-muted/70"
              title="Download logs as text file"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[110px]">
                <div className="flex items-center">
                  <Download className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[100px] group-hover:opacity-100">
                    Download Logs
                  </span>
                </div>
                <kbd className="ml-1.5 rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50">
                  D
                </kbd>
              </div>
            </Button>

            <Button
              variant="ghost"
              size="sm"
              onClick={onClearLogs}
              className="group relative z-30 h-full rounded-none px-3 py-2 text-destructive transition-all duration-300 hover:bg-destructive/10"
              title="Clear all logs"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[90px]">
                <div className="flex items-center">
                  <Trash2 className="size-4 shrink-0" />
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs text-destructive opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[80px] group-hover:opacity-100">
                    Clear Logs
                  </span>
                </div>
                <kbd className="ml-1.5 rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50">
                  R
                </kbd>
              </div>
            </Button>

            <Button
              variant="ghost"
              size="sm"
              onClick={onToggleTheme}
              className="group relative z-40 h-full rounded-none rounded-r-md border-l px-3 py-2 transition-all duration-300 hover:bg-muted/70"
              title="Toggle dark/light theme"
            >
              <div className="flex w-full min-w-9 items-center justify-between transition-all duration-300 ease-in-out group-hover:min-w-[90px]">
                <div className="flex items-center">
                  {isDarkMode ? (
                    <Sun className="size-4 shrink-0" />
                  ) : (
                    <Moon className="size-4 shrink-0" />
                  )}
                  <span className="ml-1.5 max-w-0 overflow-hidden whitespace-nowrap text-xs opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[80px] group-hover:opacity-100">
                    {isDarkMode ? "Light Mode" : "Dark Mode"}
                  </span>
                </div>
                <kbd className="ml-1.5 rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground opacity-100 transition-all duration-300 ease-in-out group-hover:opacity-50">
                  S
                </kbd>
              </div>
            </Button>
          </div>

          <div className="ml-2 flex md:hidden">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button
                  variant="outline"
                  size="icon"
                  aria-label="More actions"
                  className="size-9"
                >
                  <Keyboard className="size-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-60">
                <div className="p-2 text-xs font-semibold text-muted-foreground">
                  Keyboard Shortcuts
                </div>
                <DropdownMenuSeparator />
                <div className="px-2 py-1.5">
                  <div className="grid grid-cols-2 gap-y-2 text-sm">
                    <span>Auto-refresh</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      A
                    </kbd>
                    <span>Filters</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      F
                    </kbd>
                    <span>Crash Recovery</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      P
                    </kbd>
                    <span>Copy Logs</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      C
                    </kbd>
                    <span>Download Logs</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      D
                    </kbd>
                    <span>Clear Logs</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      R
                    </kbd>
                    <span>Toggle Theme</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      S
                    </kbd>
                    <span>Focus Search</span>
                    <kbd className="justify-self-end rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                      L
                    </kbd>
                  </div>
                </div>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
    </div>
  );
}
