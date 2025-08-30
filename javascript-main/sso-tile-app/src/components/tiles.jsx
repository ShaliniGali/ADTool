import { Tab, Tabs, ToastNotification } from 'carbon-components-react'
import React, { useContext, useEffect, useState } from 'react'
import { AppsContext } from '../App'
import { ACTIONS, APP_FILTER_TABS, APP_STATUS } from '../common/constants'
import { AppTile } from './app-tile'
import { AboutDialog } from './dialogs/about-dialog'
import { RegisterDialog } from './dialogs/register-dialog'
import './tiles.scss'

export const Tiles = () => {
    const [allApps, setAllApps, setFetchTrigger] = useContext(AppsContext)
    const [showAbout, setShowAbout] = useState(false)
    const [showRegister, setShowRegister] = useState(false)
    const [showRegisterSuccess, setShowRegisterSuccess] = useState(false)
    const [selectedApp, setSelectedApp] = useState({})
    const [favoriteApps, setFavoriteApps] = useState(
        allApps.filter((a) => a.favorite)
    )
    const [apps, setApps] = useState(allApps.filter((a) => !a.favorite))

    useEffect(() => {
        setFavoriteApps(allApps.filter((a) => a.favorite && a.visible))
        setApps(allApps.filter((a) => !a.favorite && a.visible))
    }, [allApps])

    const onAction = (action, app) => {
        if (
            app.status !== APP_STATUS.REGISTERED &&
            app.status !== APP_STATUS.NOT_REGISTERED
        ) {
            action = ACTIONS.ABOUT
        }

        switch (action) {
            case ACTIONS.ABOUT:
                setShowAbout(true)
                setShowRegister(false)
                document.addEventListener('keydown', escKeyCallback)
                break
            case ACTIONS.REGISTER:
                setShowAbout(false)
                setShowRegister(true)
                document.addEventListener('keydown', escKeyCallback)
                break
            case ACTIONS.TILE_CLICK:
            case ACTIONS.LAUNCH:
                if (app.status === APP_STATUS.REGISTERED) {
                    window.open(app.url, '_blank')
                }
                break
            default:
        }

        setSelectedApp(app)
    }

    const onClose = (message) => {
        setShowAbout(false)
        setShowRegister(false)
        setSelectedApp({})
        document.removeEventListener('keydown', escKeyCallback, false)
        if (message === 'register_success') {
            setShowRegisterSuccess(true)
            setFetchTrigger(new Date())
            setTimeout(() => {
                setShowRegisterSuccess(false)
            }, 3000)
        }
    }

    const escKeyCallback = (event) => {
        if (event.key === 'Escape') {
            setShowAbout(false)
            setShowRegister(false)
            document.removeEventListener('keydown', escKeyCallback, false)
        }
    }

    const onAppFilter = ({ selectedIndex }) => {
        const tabKey = APP_FILTER_TABS[selectedIndex]?.k || 'all'
        const apps = allApps.map((a) => {
            return {
                ...a,
                visible: tabKey === 'all' || tabKey === a.group,
            }
        })
        setAllApps(apps)
        setShowAbout(false)
        setShowRegister(false)
    }

    return (
        <div
            className={`tiles-container ${
                showAbout || showRegister ? 'has-dialog' : ''
            }`}
        >
            <div
                className={allApps.length ? 'app-filter' : 'app-filter d-none'}
            >
                <Tabs onChange={onAppFilter}>
                    {APP_FILTER_TABS.map((t) => (
                        <Tab key={t.k} label={t.label} />
                    ))}
                </Tabs>
                <div className="search-container"></div>
            </div>
            <div className="d-flex flex-grow-1 position-relative">
                {showAbout && (
                    <AboutDialog
                        app={selectedApp}
                        onClose={onClose}
                        onAction={onAction}
                    ></AboutDialog>
                )}
                {showRegister && (
                    <RegisterDialog
                        app={selectedApp}
                        onClose={onClose}
                        onAction={onAction}
                    ></RegisterDialog>
                )}
                <div
                    className={`favorite-app-tiles ${
                        favoriteApps.length === 0 ? 'no-favs' : ''
                    }`}
                >
                    {favoriteApps.map((a) => (
                        <AppTile app={a} key={a.key} onAction={onAction} />
                    ))}
                </div>
                <div className="app-tiles" data-testid="app-tiles-wrapper" style={{'gridAutoRows': `minmax(${100 / Math.ceil(apps.length / 4)}%, auto)`}}>
                    {apps.map((a) => (
                        <AppTile app={a} key={a.key} onAction={onAction} />
                    ))}
                </div>
            </div>
            {showRegisterSuccess && (
                <div className="toast-container">
                    <ToastNotification
                        kind="success"
                        lowContrast={true}
                        aria-label="Registration Successful Notification"
                        title={`Registration Successful`}
                    />
                </div>
            )}
        </div>
    )
}
