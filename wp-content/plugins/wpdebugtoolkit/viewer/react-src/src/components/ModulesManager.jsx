import React, { useState, useEffect } from "react";
import {
  AlertCircle,
  Check,
  ExternalLink,
  Loader2,
  Power,
  PowerOff,
  PlugIcon,
  Paintbrush,
} from "lucide-react";
import { useToast } from "./ui/use-toast";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "./ui/dialog";
import { Button } from "./ui/button";
import { Badge } from "./ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { getModulesStatus, togglePlugins, toggleThemes } from "../services/api";

export function ModulesManager({ isOpen, onClose }) {
  const [isLoading, setIsLoading] = useState(true);
  const [status, setStatus] = useState(null);
  const [operation, setOperation] = useState(null);
  const [error, setError] = useState(null);
  const { toast } = useToast();

  // Fetch modules status on open
  useEffect(() => {
    if (!isOpen) return;

    setIsLoading(true);
    setError(null);

    const fetchData = async () => {
      try {
        const statusData = await getModulesStatus();
        if (statusData && Object.keys(statusData).length > 0) {
          setStatus(statusData);
        } else {
          setError("No module status data received");
        }
      } catch (err) {
        setError("Failed to fetch modules status");
        console.error(err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [isOpen]);

  const handleTogglePlugins = async (enable) => {
    if (operation) return;

    setOperation({
      type: "plugins",
      action: enable ? "enable" : "disable",
      running: true,
    });

    try {
      const success = await togglePlugins(enable);

      if (!success) {
        setError(`Failed to ${enable ? "enable" : "disable"} plugins`);
        toast({
          title: "Error",
          description: `Failed to ${enable ? "enable" : "disable"} plugins`,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Success",
          description: `Plugins ${
            enable ? "enabled" : "disabled"
          } successfully`,
        });

        // Refetch status
        const statusData = await getModulesStatus();
        if (statusData && Object.keys(statusData).length > 0) {
          setStatus(statusData);
        }
      }
    } catch (err) {
      setError(`Failed to ${enable ? "enable" : "disable"} plugins`);
      console.error(err);
    } finally {
      setOperation(null);
    }
  };

  const handleToggleThemes = async (enable) => {
    if (operation) return;

    setOperation({
      type: "themes",
      action: enable ? "enable" : "disable",
      running: true,
    });

    try {
      const success = await toggleThemes(enable);

      if (!success) {
        setError(`Failed to ${enable ? "enable" : "disable"} themes`);
        toast({
          title: "Error",
          description: `Failed to ${enable ? "enable" : "disable"} themes`,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Success",
          description: `Themes ${enable ? "enabled" : "disabled"} successfully`,
        });

        // Refetch status
        const statusData = await getModulesStatus();
        if (statusData && Object.keys(statusData).length > 0) {
          setStatus(statusData);
        }
      }
    } catch (err) {
      setError(`Failed to ${enable ? "enable" : "disable"} themes`);
      console.error(err);
    } finally {
      setOperation(null);
    }
  };

  const renderPluginsSection = () => {
    if (!status || !status.plugins) return null;

    return (
      <div className="space-y-4">
        <div className="mb-2 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <PlugIcon className="size-5 text-primary" />
            <h3 className="text-lg font-semibold">Plugins</h3>
          </div>
          <Badge variant={status.plugins.enabled ? "success" : "warning"}>
            {status.plugins.enabled ? "Active" : "Disabled"}
          </Badge>
        </div>

        <div className="rounded-lg border bg-card p-4 shadow-sm">
          <div className="flex flex-col gap-4">
            <div className="flex items-center gap-2">
              <div className="size-2 rounded-full bg-primary" />
              <p className="text-sm">
                {status.plugins.enabled
                  ? `${status.plugins.count} active plugins found`
                  : "Plugins are currently disabled"}
              </p>
            </div>

            <div
              className={`rounded-md p-4 text-sm ${
                status.plugins.enabled
                  ? "bg-muted"
                  : "border border-amber-200/30 bg-amber-100/10"
              }`}
            >
              {status.plugins.enabled
                ? "Disabling plugins will temporarily rename the plugins directory, causing all plugins to be deactivated in WordPress."
                : "The plugins directory has been renamed and all plugins are currently disabled. If you open the WordPress plugins right now, you won't find any. Enabling the plugins again will revert the directory to its default state, and you'll need to re-enable your plugins again."}
            </div>

            <div className="mt-1 flex gap-3">
              <Button
                variant="outline"
                size="sm"
                onClick={() => window.open(status.plugins.admin_url, "_blank")}
                className="gap-2"
              >
                <ExternalLink className="size-3.5" />
                Open WordPress Plugins
              </Button>

              {status.plugins.enabled ? (
                <Button
                  variant="destructive"
                  size="sm"
                  onClick={() => handleTogglePlugins(false)}
                  disabled={!!operation}
                  className="gap-2"
                >
                  {operation?.type === "plugins" &&
                  operation?.action === "disable" ? (
                    <Loader2 className="size-3.5 animate-spin" />
                  ) : (
                    <PowerOff className="size-3.5" />
                  )}
                  Disable Plugins
                </Button>
              ) : (
                <Button
                  variant="default"
                  size="sm"
                  onClick={() => handleTogglePlugins(true)}
                  disabled={!!operation}
                  className="gap-2"
                >
                  {operation?.type === "plugins" &&
                  operation?.action === "enable" ? (
                    <Loader2 className="size-3.5 animate-spin" />
                  ) : (
                    <Power className="size-3.5" />
                  )}
                  Enable Plugins
                </Button>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  };

  const renderThemesSection = () => {
    if (!status || !status.themes) return null;

    return (
      <div className="space-y-4">
        <div className="mb-2 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Paintbrush className="size-5 text-primary" />
            <h3 className="text-lg font-semibold">Themes</h3>
          </div>
          <Badge variant={status.themes.enabled ? "success" : "warning"}>
            {status.themes.enabled ? "Active" : "Disabled"}
          </Badge>
        </div>

        <div className="rounded-lg border bg-card p-4 shadow-sm">
          <div className="flex flex-col gap-4">
            <div className="flex items-center gap-2">
              <div className="size-2 rounded-full bg-primary" />
              <p className="text-sm">
                {status.themes.enabled
                  ? `${status.themes.count} active themes found`
                  : "Themes are currently disabled"}
              </p>
            </div>

            <div
              className={`rounded-md p-4 text-sm ${
                status.themes.enabled
                  ? "bg-muted"
                  : "border border-amber-200/30 bg-amber-100/10"
              }`}
            >
              {status.themes.enabled
                ? "Disabling themes will temporarily rename the themes directory, causing WordPress to fall back to its basic layout."
                : "The themes directory has been renamed and themes are currently disabled. If you open the WordPress themes right now, you won't find any. Enabling the themes again will revert the directory to its default state, and you'll need to re-enable your theme again."}
            </div>

            <div className="mt-1 flex gap-3">
              <Button
                variant="outline"
                size="sm"
                onClick={() => window.open(status.themes.admin_url, "_blank")}
                className="gap-2"
              >
                <ExternalLink className="size-3.5" />
                Open WordPress Themes
              </Button>

              {status.themes.enabled ? (
                <Button
                  variant="destructive"
                  size="sm"
                  onClick={() => handleToggleThemes(false)}
                  disabled={!!operation}
                  className="gap-2"
                >
                  {operation?.type === "themes" &&
                  operation?.action === "disable" ? (
                    <Loader2 className="size-3.5 animate-spin" />
                  ) : (
                    <PowerOff className="size-3.5" />
                  )}
                  Disable Themes
                </Button>
              ) : (
                <Button
                  variant="default"
                  size="sm"
                  onClick={() => handleToggleThemes(true)}
                  disabled={!!operation}
                  className="gap-2"
                >
                  {operation?.type === "themes" &&
                  operation?.action === "enable" ? (
                    <Loader2 className="size-3.5 animate-spin" />
                  ) : (
                    <Power className="size-3.5" />
                  )}
                  Enable Themes
                </Button>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-xl">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2 text-xl">
            <Power className="size-5 text-primary" />
            WordPress Modules Manager
          </DialogTitle>
          <DialogDescription>
            Disable all plugins and themes to troubleshoot WordPress issues.
          </DialogDescription>
        </DialogHeader>

        {isLoading ? (
          <div className="flex items-center justify-center p-8">
            <Loader2
              className="size-8 animate-spin text-primary"
              role="status"
              aria-label="Loading"
            />
          </div>
        ) : error ? (
          <div className="my-4 rounded-md border border-destructive bg-destructive/20 p-3">
            <div className="flex gap-2">
              <AlertCircle className="size-5 text-destructive" />
              <div>
                <div className="font-medium text-destructive">Error</div>
                <div className="text-sm text-destructive-foreground">
                  {error}
                </div>
              </div>
            </div>
          </div>
        ) : (
          status && (
            <Tabs defaultValue="plugins" className="py-4">
              <TabsList className="mb-6 grid w-full grid-cols-2">
                <TabsTrigger
                  value="plugins"
                  className="flex items-center gap-2"
                >
                  <PlugIcon className="size-4" />
                  Plugins
                </TabsTrigger>
                <TabsTrigger value="themes" className="flex items-center gap-2">
                  <Paintbrush className="size-4" />
                  Themes
                </TabsTrigger>
              </TabsList>
              <TabsContent value="plugins">
                {renderPluginsSection()}
              </TabsContent>
              <TabsContent value="themes">{renderThemesSection()}</TabsContent>
            </Tabs>
          )
        )}

        <DialogFooter className="mt-3 border-t pt-3">
          {status && status.wp_admin_url && (
            <div className="mr-auto flex items-center text-sm text-muted-foreground">
              <Check className="mr-2 size-4 text-green-500" />
              <span>
                WordPress Admin:{" "}
                <a
                  href={status.wp_admin_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="underline"
                >
                  {status.wp_admin_url}
                </a>
              </span>
            </div>
          )}
          <Button onClick={onClose}>Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
