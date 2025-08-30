import React from 'react'
import { APP_STATUS } from '../../common/constants'

export const RegisterStatus = ({ status }) => {
    let statusElem = null
    switch (status) {
        case APP_STATUS.REGISTERED:
            statusElem = (
                <span className="badge ml-2 badge-success">{status}</span>
            )
            break
        case APP_STATUS.PENDING:
        case APP_STATUS.NOT_REGISTERED:
        case APP_STATUS.COMING_SOON:
            statusElem = (
                <span className="badge ml-2 badge-warning">{status}</span>
            )
            break

        default:
            return null
    }

    return (
        <div className="mt-3 d-flex align-items-center" data-testid="register-status">
            <strong>Registration Status:</strong>
            {statusElem}
        </div>
    )
}
