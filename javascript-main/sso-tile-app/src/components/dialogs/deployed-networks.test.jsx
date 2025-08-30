import { DeployedNetworks } from "./deployed-networks";

describe('deployed Networks', () => {
    test('deployed networks', () => {
        let deployed = ["NIPR", "SIPR", "JWICS", "COMMING_SOON"];
        let dn = DeployedNetworks({deployed_networks: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed networks', () => {
        let deployed = ["SIPR", "JWICS", "COMMING_SOON"];
        let dn = DeployedNetworks({deployed_networks: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed networks', () => {
        let deployed = ["JWICS", "COMMING_SOON"];
        let dn = DeployedNetworks({deployed_networks: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed networks', () => {
        let deployed = ["JWICS", "COMMING_SOON"];
        let dn = DeployedNetworks({deployed_networks: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed networks', () => {
        let deployed = ["COMING_SOON"];
        let dn = DeployedNetworks({deployed_networks: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed networks', () => {
        let deployed = ["1"];
        let dn = DeployedNetworks({deployed_networks: deployed});
    
        expect(dn).toBeNull();
    });
});