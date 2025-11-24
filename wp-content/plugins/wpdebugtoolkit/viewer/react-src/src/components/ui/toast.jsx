import React, { createContext, useContext, useCallback, useState } from "react";
import { X } from "lucide-react";
import { cn } from "@/lib/utils";

const ToastContext = createContext({
  toast: () => {},
  dismiss: () => {},
  toasts: [],
});

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error("useToast must be used within a ToastProvider");
  }
  return context;
}

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  const toast = useCallback(({ description, duration = 2000, icon }) => {
    const id = Math.random().toString(36).slice(2, 9);
    setToasts((prev) => [...prev, { id, description, icon }]);

    if (duration !== Infinity) {
      setTimeout(() => {
        setToasts((prev) => prev.filter((toast) => toast.id !== id));
      }, duration);
    }

    return id;
  }, []);

  const dismiss = useCallback((toastId) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== toastId));
  }, []);

  return (
    <ToastContext.Provider value={{ toast, dismiss, toasts }}>
      {children}
      <div className="fixed bottom-0 right-0 z-50 flex w-full max-w-md flex-col items-end space-y-2 p-4">
        {toasts.map(({ id, description, icon }) => (
          <div
            key={id}
            className={cn(
              "bg-background border rounded-lg shadow-lg p-4 pr-8 relative flex items-center gap-2",
              "animate-in slide-in-from-bottom-5 fade-in duration-300",
              "data-[state=closed]:animate-out data-[state=closed]:slide-out-to-right-5 data-[state=closed]:fade-out",
            )}
          >
            {icon && <div className="text-[#C7337E]">{icon}</div>}
            <p className="text-sm text-foreground">{description}</p>
            <button
              onClick={() => dismiss(id)}
              className="absolute right-2 top-2 opacity-70 transition-opacity hover:opacity-100"
            >
              <X className="size-4" />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}
