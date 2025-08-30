import React from 'react'
import './footer.scss'

export const Footer = () => {
    return (
        <footer className="p-2">
            <ul className="list-inline">
                <li>
                    <a
                        href=""
                        rel="noreferrer"
                        className="btn text-muted"
                        target="_blank"
                        title="About Rhombus"
                    >
                        About
                    </a>
                </li>
                <li>
                    <a
                        href=""
                        className="btn text-muted"
                    >
                        Support
                    </a>
                </li>
                <li>
                    <a
                        href=""
                        rel="noreferrer"
                        className="btn text-muted"
                        target="_blank"
                        title="Contact Rhombus"
                    >
                        Contact
                    </a>
                </li>
                <li>
                    <a
                        href="/login/logout"
                        rel="noreferrer"
                        className="btn text-muted"
                        title="Logout"
                    >
                        Logout
                    </a>
                </li>
            </ul>
        </footer>
    )
}
