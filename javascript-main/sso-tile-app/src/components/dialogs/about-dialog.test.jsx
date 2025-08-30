import { render, screen, fireEvent, cleanup } from "@testing-library/react";
import { ACTIONS, APP_STATUS } from "../../common/constants";
import { AboutDialog } from "./about-dialog";

const APP = {
    key: "sb",
    label: "Strategic Basing",
    icon: "app-10.svg",
    status: "SIPR",
    url: "https://dev-actf.rhombuspower.com/",
    color: 1,
    group: "a1",
    favorite: false,
    description:
        "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    deployed_networks: ["SIPR"],
    deployed_environments: ["P1"],
    deployment: ['NIPR']
};

let testAction;
let testApp;

const onAction = (action, app) => {
    testAction = action;
    testApp = app;
};
const onClose = jest.fn();

beforeEach(() => {
    render(<AboutDialog app={APP} onAction={onAction} onClose={onClose} />);
});

afterEach(cleanup);

test("reder app label", () => {
    expect(screen.getByText(APP.label)).toBeInTheDocument();
});

test("reder app description", () => {
    expect(screen.getByText(APP.description)).toBeInTheDocument();
});

test("show deployment notification", () => {
    expect(screen.getByText(/P1/i)).toBeInTheDocument();
});

test("click close button", () => {
    fireEvent.click(screen.getByText(/CLOSE/i));
    expect(onClose).toBeCalled();
});

test("click launch button", () => {
    fireEvent.click(screen.getByText('LAUNCH'));
    expect(testAction).toBe(ACTIONS.LAUNCH);
});

test("hide launch button", () => {
    cleanup();
    render(<AboutDialog app={{ ...APP, status: APP_STATUS.NOT_REGISTERED }} onAction={onAction} onClose={onClose} />);
    expect(screen.getByText(/LAUNCH/i).classList.contains("d-none")).toBe(true);
});

test("click register button", () => {
    fireEvent.click(screen.getByText('REGISTER'));
    expect(testAction).toBe(ACTIONS.REGISTER);
});

test("hide register button", () => {
    cleanup();
    render(<AboutDialog app={{ ...APP, status: APP_STATUS.REGISTERED }} onAction={onAction} onClose={onClose} />);
    expect(screen.getByText('REGISTER').classList.contains("d-none")).toBe(true);
});
