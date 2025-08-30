import React from 'react'

export const DeployedNetworks = ({ deployed_networks }) => {
    let networksElem = null
    let currNetwork = deployed_networks[0];
    switch (currNetwork) {
        case 'SIPR':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">NIPR</span>
                    <span className="badge ml-2 badge-red">SIPR</span>
                    <span className="badge ml-2 badge-grey">JWICS</span>
                </span>
            )
            break
        case 'NIPR':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-success">NIPR</span>
                    <span className="badge ml-2 badge-grey">SIPR</span>
                    <span className="badge ml-2 badge-grey">JWICS</span>
                </span>
            )
            break
        case 'JWICS':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">NIPR</span>
                    <span className="badge ml-2 badge-grey">SIPR</span>
                    <span className="badge ml-2 badge-orange">JWICS</span>
                </span>
            )
            break
        case 'COMING_SOON':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">NIPR</span>
                    <span className="badge ml-2 badge-grey">SIPR</span>
                    <span className="badge ml-2 badge-grey">JWICS</span>
                </span>
            )
            break

        default:
            return null
    }

    return (
        <div className="mt-3 d-flex align-items-center" data-testid="deployed-networks">
            <strong>Deployed Networks:</strong>
            {networksElem}
        </div>
    )
}
