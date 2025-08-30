import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { AppsContext } from "../App";
import { Tiles } from "./tiles";

const APPS = [
    {
        key: "sb",
        label: "Strategic Basing",
        icon: "app-10.svg",
        status: "REGISTERED",
        url: "https://dev-actf.rhombuspower.com/",
        color: 1,
        group: "a1",
        favorite: true,
        visible: true,
        description:
            "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
    {
        key: "capdev",
        label: "Capability Development",
        icon: "app-12.svg",
        status: "NOT_REGISTERED",
        url: "https://dev-actf.rhombuspower.com/",
        color: 3,
        group: "a2",
        favorite: false,
        description:
            "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
];

let newApp;
let tileComponent;
const setApps = (apps) => {
    newApp = apps[0];
};

const setFetchTrigger = (apps) => {
    newApp = apps[0];
};

const value = [APPS, setApps, setFetchTrigger];

beforeEach(() => {
    tileComponent = render(
        <AppsContext.Provider value={value}>
            <Tiles />
        </AppsContext.Provider>
    );
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

test("renders component tabs", () => {
    expect(screen.getByText("How Do We Pay for This Option?")).toBeInTheDocument();
});

test("renders 5 tabs", () => {
    expect(screen.getAllByRole("tab").length).toBe(6);
});


test("Shows register page and adds event listener when register button is clicked", () => {
    window.addEventListener = jest.fn();
    fireEvent.click(screen.getByText("REGISTER"));
    expect(window.addEventListener).toHaveBeenCalled();
    expect(screen.getByText("SUBMIT", { exact: false })).toBeInTheDocument();
});

test("Filters the apps on tab change", () => {
    fireEvent.click(screen.getByText("How is Threat Evolving?"));
    expect(screen.getAllByText("Strategic Basing").length).toBe(1);
    expect(screen.queryAllByText("Capability Development").length).toBe(0);
});

test("Registration shows success message on successful registration", async () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ foo: "bar" }),
        })
    );
    fireEvent.click(screen.getByText("REGISTER"));
    expect(screen.getByText("SUBMIT")).toBeInTheDocument();

    fireEvent.click(screen.getByText("SUBMIT"));
    await waitFor(async () => {
        expect(global.fetch).toBeCalled();        
    });
    expect(screen.getByRole('status')).toBeInTheDocument();
});

test("Launch button clicked", async () => {
    window.open = jest.fn();
    fireEvent.click(screen.getByText("LAUNCH"));
    await waitFor(async () => {
        expect(window.open).toBeCalled();        
    });
});
