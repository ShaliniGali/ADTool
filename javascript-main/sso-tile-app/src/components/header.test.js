import { render, screen } from '@testing-library/react'
import { Header } from './header'

window.USER_DATA = { fullName: 'John Doe' }

test('renders image element', () => {
    render(<Header />)
    const imgEl = screen.getByAltText(/Guardian Logo/i)
    expect(imgEl).toBeInTheDocument()
})

test('renders user name', () => {
    render(<Header />)
    const elem = screen.getByText(/John Doe/i)
    expect(elem).toBeInTheDocument()
})

test('renders welcome text', () => {
    render(<Header />)
    const elem = screen.getByText(/GUARDIAN/i)
    expect(elem).toBeInTheDocument()
})
