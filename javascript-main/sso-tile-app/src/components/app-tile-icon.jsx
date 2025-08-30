import React from 'react'
import './app-tile-icon.scss'

export const AppTileIcon = ({ app }) => {
    return (
        <>
            <img className="app-icon-svg" src={'./static/images/' + app.icon} alt={app.label + ' Icon'} />
        </>
    )
}
