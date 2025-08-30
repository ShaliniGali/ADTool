import React from 'react'

export const DeployedEnvironments = ({ deployed_environments }) => {
    let networksElem = null
    let currEnvironment = deployed_environments[0];
    switch (currEnvironment) {
        case 'VAULT':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-blue">VAULT</span>
                    <span className="badge ml-2 badge-grey">P1</span>
                    <span className="badge ml-2 badge-grey">CloudWorks</span>
                </span>
            )
            break
        case 'P1':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">VAULT</span>
                    <span className="badge ml-2 badge-blue">P1</span>
                    <span className="badge ml-2 badge-grey">CloudWorks</span>
                </span>
            )
            break
        case 'CloudWorks':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">VAULT</span>
                    <span className="badge ml-2 badge-grey">P1</span>
                    <span className="badge ml-2 badge-blue">CloudWorks</span>
                </span>
            )
            break
        case 'COMING_SOON':
            networksElem = (
                <span>
                    <span className="badge ml-2 badge-grey">VAULT</span>
                    <span className="badge ml-2 badge-grey">P1</span>
                    <span className="badge ml-2 badge-grey">CloudWorks</span>
                </span>
            )
            break
        default:
            return null
    }

    return (
        <div className="mt-3 d-flex align-items-center" data-testid="deployed-environments">
            <strong>Deployed Environments:</strong>
            {networksElem}
        </div>
    )
}
