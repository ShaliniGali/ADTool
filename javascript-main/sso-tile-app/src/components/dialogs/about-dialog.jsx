
import React from 'react'
import { ACTIONS, APP_STATUS } from '../../common/constants'
import { isAppDeployed } from '../../common/utilities'
import { AppTileIcon } from '../app-tile-icon'
import './about-dialog.scss'
import { RegisterStatus } from './register-status'
import { DeployedNetworks } from './deployed-networks'
import { DeployedEnvironments } from './deployed-environments'

export const AboutDialog = ({ app, onClose, onAction }) => {
    return (
        <div className="dialog about-dialog">
            <div className="title">
                <h2>{app.label}</h2>
                <AppTileIcon app={app} />
            </div>
            <div className="description">
                <span dangerouslySetInnerHTML={{ __html: app.description }}></span>
                <RegisterStatus status={app.status}></RegisterStatus>
                <DeployedNetworks deployed_networks={app.deployed_networks}></DeployedNetworks>
                <DeployedEnvironments deployed_environments={app.deployed_environments}></DeployedEnvironments>
            </div>
            <div
                className={`my-3 ${isAppDeployed(app.status) ? 'd-none' : ''}`}
            >
                <div
                    className="alert alert-warning"
                    aria-label="closes notification"
                    title={app.deployment.includes('COMING_SOON') ? `Coming soon` : `Please contact Rhombus help desk to request access`}
                >
                    {app.deployment.includes('COMING_SOON') ? `Coming soon` : `Please contact Rhombus help desk to request access`}
                </div>
            </div>
            <div className="buttons">
                <button
                    className="btn btn-sm btn-outline-primary"
                    onClick={() => onClose()}
                >
                    CLOSE
                </button>
                <button
                    className={`btn btn-sm btn-warning ${
                        app.status === APP_STATUS.REGISTERED ? '' : 'd-none'
                    }`}
                    onClick={() => onAction(ACTIONS.LAUNCH, app)}
                >
                    LAUNCH
                </button>
                <button
                    className={`btn btn-sm btn-primary ${
                        app.status === APP_STATUS.NOT_REGISTERED ? '' : 'd-none'
                    }`}
                    onClick={() => onAction(ACTIONS.REGISTER, app)}
                >
                    REGISTER
                </button>
            </div>
        </div>
    )
}
