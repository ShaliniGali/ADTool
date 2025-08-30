import { useContext } from "react";
import { AppsContext } from "../App";
import { ACTIONS, APP_STATUS } from "../common/constants";
import { convertToFormData, getCSRFToken } from "../common/utilities";
import { AppTileButtons } from "./app-tile-buttons";
import { AppTileIcon } from "./app-tile-icon";
import "./app-tile.scss";

/**
 * Save list of favorite apps to the server
 * @param ids array of app keys
 */
export const saveFavorites = async (ids) => {
    sessionStorage.setItem("favApps", JSON.stringify(ids));
    return fetch("/api/sso/favorites", {
        method: "POST",
        body: convertToFormData({ ids, rhombus_token: getCSRFToken() }),
    })
        .then((res) => {
            if (res.ok) {
                return res.json();
            }
            throw new Error("Favorites save failed");
        })
        .catch((error) => console.error(error));
};

export const AppTile = ({ app, onAction }) => {
    const [allApps, setAllApps] = useContext(AppsContext);

    const toggleFavorite = (e) => {
        e.stopPropagation();
        const apps = allApps.map((a) => {
            const favorite = a.key === app.key ? !a.favorite : a.favorite;
            return {
                ...a,
                favorite,
            };
        });

        const favApps = apps.filter((a) => a.favorite);
        if (favApps.length > 4) {
            alert("Maximum 4 apps can be added to favorites.");
            return;
        }

        setAllApps(apps);
        saveFavorites(favApps.map((a) => a.key));
    };

    return (
        <div
            className={`app-tile ${app.status === APP_STATUS.REGISTERED ? "" : "not-registered"}`}
            onClick={() => onAction(ACTIONS.TILE_CLICK, app)}
            data-testid={`app-tile-${app.key}`}
        >
            <a href={`#fav`} className="btn btn-sm fav-btn" onClick={toggleFavorite} data-testid={`toggle-fav-${app.key}`}>
                <span className={app.favorite ? `fas fa-star` : `far fa-star`}></span>
            </a>
            <div className="app-icon">
                <AppTileIcon app={app} />
            </div>
            <div className="app-label">{app.label}</div>
            <div className="app-tile-buttons">
                <AppTileButtons app={app} onAction={onAction}></AppTileButtons>
            </div>
        </div>
    );
};
