import { cleanup, fireEvent, render, screen, waitFor } from "@testing-library/react";
import { AppsContext } from "../App";
import { AppTile, saveFavorites } from "./app-tile";
import { ACTIONS } from "../common/constants";

const APP = {
    key: "sb",
    label: "Strategic Basing",
    icon: "app-10.svg",
    status: "REGISTERED",
    url: "https://dev-actf.rhombuspower.com/",
    color: 1,
    group: "a1",
    favorite: false,
    description: "Lorem ",
};
const APPS = [1,2,3,4].map((i) => ({ ...APP, key: "sb" + i, favorite: true }));
APPS.push(APP);
APPS[3].favorite = false;

let testAction;
let testApp;
let newApps;

const setApps = (apps) => {
    newApps = apps;
};

const onAction = (action, app) => {
    testAction = action;
    testApp = app;
};

window.alert = jest.fn();

const value = [APPS, setApps]

beforeEach(()=> {
    render(
        <AppsContext.Provider value={value}>
            <AppTile app={APP} onAction={onAction} />
        </AppsContext.Provider>
    );
})

test("renders component with app label", () => {
    expect(screen.getByText(APP.label)).toBeInTheDocument();
});

test("click executes tile click action", () => {
    const elem = screen.getByTestId(`app-tile-${APP.key}`);
    fireEvent.click(elem);

    expect(testAction).toBe(ACTIONS.TILE_CLICK);
    expect(testApp).toBe(APP);
});

test("toggle favorite and save to session storage", () => {
    const elem = screen.getByTestId(`toggle-fav-${APP.key}`);
    fireEvent.click(elem);

    expect(newApps[4].favorite).toBe(!APP.favorite);
    expect(sessionStorage.getItem("favApps")).toBe('["sb1","sb2","sb3","sb"]');
});

test("error when more than 4 favorite apps", () => {
    cleanup()
    APPS[3].favorite = true;
    const value = [APPS, setApps]
    render(
        <AppsContext.Provider value={value}>
            <AppTile app={APP} onAction={onAction} />
        </AppsContext.Provider>
    );
    const elem = screen.getByTestId(`toggle-fav-${APP.key}`);
    fireEvent.click(elem);

    expect(window.alert).toBeCalled();
});

test("test save favorite function ok", async () => {
    jest.spyOn(global, "fetch").mockResolvedValue({
        ok: true,
        json: jest.fn().mockResolvedValue({ foo: "bar" }),
    });
    const result = await saveFavorites(["sb"]);
    await waitFor(() => expect(result).toMatchObject({ foo: "bar" }));
});

test("test save favorite function error", async () => {
    console.error = jest.fn();
    jest.spyOn(global, "fetch").mockResolvedValue({
        ok: false,
        json: jest.fn().mockResolvedValue({ foo: "bar" }),
    });
    await saveFavorites(["sb"]);
    await waitFor(() => expect(console.error).toBeCalled());
});
