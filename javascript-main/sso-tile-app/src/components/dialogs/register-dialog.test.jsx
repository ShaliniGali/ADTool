import { cleanup, fireEvent, render, screen, waitFor, act } from "@testing-library/react";
import { AppsContext } from "../../App";
import { getCSRFToken } from "../../common/utilities";
import { RegisterDialog } from "./register-dialog";

const APPS = [
    {
        key: "sb1",
        label: "Strategic Basing",
        icon: "app-10.svg",
        status: "NOT_REGISTERED",
        url: "https://dev-actf.rhombuspower.com/",
        color: 1,
        group: "a1",
        favorite: false,
        description: "Lorem",
    },
    {
        key: "sb2",
        label: "Strategic Basing",
        icon: "app-10.svg",
        status: "NOT_REGISTERED",
        url: "https://dev-actf.rhombuspower.com/",
        color: 1,
        group: "a1",
        favorite: false,
        description: "Lorem ",
    },
];
const onClose = jest.fn();

let newApp;
const setApps = (apps) => {
    newApp = apps[0];
};

jest.mock("../../common/utilities", () => ({
    ...jest.requireActual("../../common/utilities"),
    getCSRFToken: jest.fn(),
}));

beforeEach(() => {
    render(
        <AppsContext.Provider value={[APPS, setApps]}>
            <RegisterDialog app={APPS[0]} onClose={onClose} />
        </AppsContext.Provider>
    );
});

afterEach(cleanup);

test("render register header", () => {
    expect(screen.getByText("Register")).toBeInTheDocument();
});

test("render inputs", () => {
    expect(screen.getByLabelText("Full Name")).toBeInTheDocument();
    expect(screen.getByLabelText("Email Address")).toBeInTheDocument();
    expect(screen.getByLabelText("Department")).toBeInTheDocument();
    expect(screen.getByLabelText("Role")).toBeInTheDocument();
    expect(screen.getByText("Application")).toBeInTheDocument();
    expect(screen.getByTestId("register-form")).toBeInTheDocument();
});

test("click close button", () => {
    fireEvent.click(screen.getByText("CLOSE"));
    expect(onClose).toBeCalled();
});

test("register error", async () => {
    jest.spyOn(global, "fetch").mockResolvedValue({
        ok: false,
        json: jest.fn().mockResolvedValue({ foo: "bar" }),
    });
    fireEvent.click(screen.getByText("SUBMIT"));
    await waitFor(() => expect(screen.getByRole("status")).toBeInTheDocument());
});

test("close after register", async () => {
    jest.spyOn(global, "fetch").mockResolvedValue({
        ok: true,
        json: jest.fn().mockResolvedValue({ foo: "bar" }),
    });
    fireEvent.click(screen.getByText("SUBMIT"));
    await waitFor(() => {
        expect(onClose).toBeCalled();
        expect(getCSRFToken).toBeCalled();
    });
});
