import React from 'react'
import './header.scss'

export const Header = () => {
    return (
        <header className="p-3">
            <img src="./static/images/guardian_logo_70x70.png" alt="Guardian Logo" />
            <div className="pl-3">
                <div className="username">Hi {window.USER_DATA?.fullName},</div>
                <div className='welcome'>
                    Welcome to <span className="text-primary fw-bold">GUARDIAN</span>!
                </div>
            </div>
        </header>
    )
}
