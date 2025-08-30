import { fireEvent, render, screen } from '@testing-library/react'
import { ACTIONS, APP_STATUS } from '../common/constants'
import { AppTileButtons } from './app-tile-buttons'

const APP = {
    key: 'sb',
    label: 'Strategic Basing',
    icon: 'app-10.svg',
    status: 'REGISTERED',
    url: 'https://dev-actf.rhombuspower.com/',
    color: 1,
    group: 'a1',
    favorite: true,
    description:
        "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
}

let testAction
let testApp

function onAction(action, app) {
    testAction = action
    testApp = app
}

test('renders buttons', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    expect(screen.getByText(/ABOUT/i)).toBeInTheDocument()
    expect(screen.getByText(/LAUNCH/i)).toBeInTheDocument()
    expect(screen.getByText(/REGISTER/i)).toBeInTheDocument()
})

test('clicks about button', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    fireEvent.click(screen.getByText(/ABOUT/i))
    expect(testAction).toBe(ACTIONS.ABOUT)
    expect(testApp).toBe(APP)
})

test('clicks launch button', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    fireEvent.click(screen.getByText(/LAUNCH/i))
    expect(testAction).toBe(ACTIONS.LAUNCH)
    expect(testApp).toBe(APP)
})

test('launch button should be hidden', () => {
    let newApp = { ...APP, status: APP_STATUS.NOT_REGISTERED }
    render(<AppTileButtons app={newApp} onAction={onAction} />)
    const button = screen.getByText(/LAUNCH/i)
    expect(button.classList.contains('d-none')).toBe(true)
})

test('launch button should be visible', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    const button = screen.getByText(/LAUNCH/i)
    expect(button.classList.contains('d-none')).toBe(false)
})

test('clicks register button', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    fireEvent.click(screen.getByText(/REGISTER/i))
    expect(testAction).toBe(ACTIONS.REGISTER)
    expect(testApp).toBe(APP)
})

test('register button should be hidden', () => {
    render(<AppTileButtons app={APP} onAction={onAction} />)
    const button = screen.getByText(/REGISTER/i)
    expect(button.classList.contains('d-none')).toBe(true)
})

test('register button should be visible', () => {
    let newApp = { ...APP, status: APP_STATUS.NOT_REGISTERED }
    render(<AppTileButtons app={newApp} onAction={onAction} />)
    const button = screen.getByText(/REGISTER/i)
    expect(button.classList.contains('d-none')).toBe(false)
})
