import { render, screen } from '@testing-library/react'
import { AppTileIcon } from './app-tile-icon'

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

test('renders image with alt text', () => {
    render(<AppTileIcon app={APP} />)
    expect(screen.getByAltText(`${APP.label} Icon`)).toBeInTheDocument()
})

test('renders correct icon', () => {
    render(<AppTileIcon app={APP} />)
    const img = screen.getByAltText(`${APP.label} Icon`)
    expect(img.getAttribute('src')).toBe(`./static/images/${APP.icon}`)
})

test('renders correct icon', () => {
    render(<AppTileIcon app={APP} />)
    const img = screen.getByAltText(`${APP.label} Icon`)
    expect(img).toBeInTheDocument()
})
