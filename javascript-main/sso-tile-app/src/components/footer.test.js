import { render, screen } from '@testing-library/react'
import { Footer } from './footer'

test('renders list elements', () => {
    render(<Footer />)
    const liElem = screen.getAllByRole('listitem')
    expect(liElem).toHaveLength(4)
})

test('renders about link', () => {
    render(<Footer />)
    const liElem = screen.getByTitle('About Rhombus')
    expect(liElem).toBeInTheDocument()
    expect(liElem.getAttribute('href')).toBe('')
})

test('renders contact link', () => {
    render(<Footer />)
    const liElem = screen.getByTitle('Contact Rhombus')
    expect(liElem).toBeInTheDocument()
    expect(liElem.getAttribute('href')).toBe('')
})

test('renders logout link', () => {
    render(<Footer />)
    const liElem = screen.getByTitle('Logout')
    expect(liElem).toBeInTheDocument()
    expect(liElem.getAttribute('href')).toBe('/login/logout')
})
