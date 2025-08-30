import { render, screen } from "@testing-library/react";
import App, { getAppData } from "./App";

const unmockedFetch = global.fetch;

test("fetch data", async () => {
    global.fetch = () =>
        Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ foo: "bar" }),
        });
    const result = await getAppData();
    expect(result).toMatchObject({ foo: "bar" });
});

test("fetch data error", async () => {
    console.error = jest.fn();
    global.fetch = () =>
        Promise.resolve({
            ok: false,
            json: () => Promise.resolve({ foo: "bar" }),
        });
    await getAppData();
    expect(console.error).toBeCalled();
});

beforeAll(() => {
    Object.defineProperty(window, "matchMedia", {
      writable: true,
      value: jest.fn().mockImplementation(query => ({
        matches: false,
        media: query,
        onchange: null,
        addListener: jest.fn(), // Deprecated
        removeListener: jest.fn(), // Deprecated
        addEventListener: jest.fn(),
        removeEventListener: jest.fn(),
        dispatchEvent: jest.fn(),
      }))
    });
});

test("Renders Header with Guardian", () => {
    render(<App />);
    expect(screen.getByText(/GUARDIAN/i)).toBeInTheDocument();
});
