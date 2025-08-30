import React from 'react'
import { ACTIONS, APP_STATUS } from '../common/constants'

export const AppTileButtons = ({ app, onAction }) => {
    return (
        <>
            <button
                className="btn btn-sm btn-outline-primary"
                onClick={(e) => {
                    e.stopPropagation()
                    onAction(ACTIONS.ABOUT, app)
                }}
            >
                ABOUT
            </button>
            <button
                className={`btn btn-sm btn-warning ${
                    app.status !== APP_STATUS.REGISTERED ? 'd-none' : ''
                }`}
                onClick={(e) => {
                    e.stopPropagation()
                    onAction(ACTIONS.LAUNCH, app)
                }}
            >
                LAUNCH
            </button>
            <button
                className={`btn btn-sm btn-primary ${
                    app.status !== APP_STATUS.NOT_REGISTERED ? 'd-none' : ''
                }`}
                onClick={(e) => {
                    e.stopPropagation()
                    onAction(ACTIONS.REGISTER, app)
                }}
            >
                REGISTER
            </button>
        </>
    )
}
