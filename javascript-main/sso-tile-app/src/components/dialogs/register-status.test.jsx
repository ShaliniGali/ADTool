import { render, screen } from "@testing-library/react";
import { APP_STATUS } from "../../common/constants";
import { RegisterStatus } from "./register-status";

test("status Pending", () => {
    render(<RegisterStatus status={APP_STATUS.PENDING} />);
    const elem = screen.getByText(APP_STATUS.PENDING);
    expect(elem).toBeInTheDocument();
});

test("status registered", () => {
    render(<RegisterStatus status={APP_STATUS.REGISTERED} />);
    const elem = screen.getByText(APP_STATUS.REGISTERED);
    expect(elem).toBeInTheDocument();
});
