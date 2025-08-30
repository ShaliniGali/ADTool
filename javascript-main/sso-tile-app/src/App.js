import { createContext, useEffect, useMemo, useState } from "react";
import "./App.scss";
import { Footer } from "./components/footer";
import { Header } from "./components/header";
import { Tiles } from "./components/tiles";
import appsData from "./apps.json";

export const AppsContext = createContext();

let ALL_APPS = window.APPS_DATA?.length ? window.APPS_DATA : appsData;

export const getAppData = () => {
    return fetch("/api/sso/apps")
        .then((res) => {
            if (res.ok) {
                return res.json();
            }
            throw new Error("Cannot fetch apps");
        })
        .then((res) => res)
        .catch((error) => console.error(error));
};

function App() {
    const [apps, setApps] = useState([]);
    const [fetchTrigger, setFetchTrigger] = useState(null);

    const value = useMemo(() => [apps, setApps, setFetchTrigger], [apps]);

    useEffect(() => {
        setApps(ALL_APPS.map((a) => ({ ...a, visible: true })));
    }, []);

    useEffect(() => {
        if (fetchTrigger === null) return;
        async function fetchData() {
            const allApps = await getAppData();
            setApps(allApps.map((a) => ({ ...a, visible: true })));
        }
        fetchData();
    }, [fetchTrigger]);

    return (
        <AppsContext.Provider value={value}>
            <Header />
            <Tiles></Tiles>
            <Footer />
        </AppsContext.Provider>
    );
}

export default App;
