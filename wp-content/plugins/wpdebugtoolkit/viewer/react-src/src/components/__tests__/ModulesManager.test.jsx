import React from "react";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import { ModulesManager } from "../ModulesManager";
import { useToast } from "../ui/use-toast";

// Mock the useToast hook
jest.mock("../ui/use-toast", () => ({
  useToast: jest.fn(),
}));

// Mock fetch globally
global.fetch = jest.fn();

describe("ModulesManager Component", () => {
  beforeEach(() => {
    // Reset mocks before each test
    global.fetch.mockClear();
    useToast.mockClear();

    // Setup default mock implementations
    useToast.mockReturnValue({
      toast: jest.fn(),
    });

    // Setup default fetch behavior
    global.fetch.mockResolvedValue({
      json: jest.fn().mockResolvedValue({
        plugins: {
          enabled: true,
          disabled: false,
          count: 5,
          admin_url: "http://example.com/wp-admin/plugins.php",
        },
        themes: {
          enabled: true,
          disabled: false,
          count: 2,
          admin_url: "http://example.com/wp-admin/themes.php",
        },
        wp_admin_url: "http://example.com/wp-admin/",
      }),
    });
  });

  it("renders the component when open", async () => {
    render(<ModulesManager isOpen={true} onClose={() => {}} />);

    // Check loading state is shown initially
    expect(screen.getByRole("status")).toBeInTheDocument();

    // Wait for data to load
    await waitFor(() => {
      expect(screen.getByText("WordPress Modules Manager")).toBeInTheDocument();
      expect(screen.getByText("Plugins")).toBeInTheDocument();
      expect(screen.getByText("Themes")).toBeInTheDocument();
    });
  });

  it("fetches module status when opened", async () => {
    render(<ModulesManager isOpen={true} onClose={() => {}} />);

    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith(
        "./api.php?action=get_modules_status",
      );
    });
  });

  it("handles disabling plugins", async () => {
    render(<ModulesManager isOpen={true} onClose={() => {}} />);

    // Wait for data to load
    await waitFor(() => {
      expect(screen.getByText("Disable Plugins")).toBeInTheDocument();
    });

    // Setup mock for disable operation
    global.fetch.mockResolvedValueOnce({
      json: jest.fn().mockResolvedValue({
        success: true,
        message: "Successfully disabled all plugins",
      }),
    });

    // Click the disable button
    fireEvent.click(screen.getByText("Disable Plugins"));

    // Check the API was called correctly
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith(
        "./api.php?action=disable_plugins",
      );
    });

    // Check toast was shown
    await waitFor(() => {
      expect(useToast().toast).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Success",
          description: "Successfully disabled all plugins",
        }),
      );
    });
  });

  it("handles error when disabling plugins fails", async () => {
    render(<ModulesManager isOpen={true} onClose={() => {}} />);

    // Wait for data to load
    await waitFor(() => {
      expect(screen.getByText("Disable Plugins")).toBeInTheDocument();
    });

    // Setup mock for failed operation
    global.fetch.mockResolvedValueOnce({
      json: jest.fn().mockResolvedValue({
        success: false,
        error: "Failed to disable plugins",
      }),
    });

    // Click the disable button
    fireEvent.click(screen.getByText("Disable Plugins"));

    // Check toast was shown with error
    await waitFor(() => {
      expect(useToast().toast).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error",
          description: "Failed to disable plugins",
          variant: "destructive",
        }),
      );
    });
  });
});
