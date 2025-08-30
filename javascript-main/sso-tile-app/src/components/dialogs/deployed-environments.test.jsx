import { DeployedEnvironments } from "./deployed-environments";

describe('deployed Environments', () => {
    test('deployed environments', () => {
        let deployed = ["VAULT", "P1", "CloudWorks", "COMMING_SOON"];
        let dn = DeployedEnvironments({deployed_environments: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed environments', () => {
        let deployed = ["P1", "CloudWorks", "COMMING_SOON"];
        let dn = DeployedEnvironments({deployed_environments: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed environments', () => {
        let deployed = ["CloudWorks", "COMMING_SOON"];
        let dn = DeployedEnvironments({deployed_environments: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed environments', () => {
        let deployed = ["COMMING_SOON"];
        let dn = DeployedEnvironments({deployed_environments: deployed});
       
        expect(dn).toBeDefined()
    });
    test('deployed environments', () => {
        let deployed = ["2"];
        let dn = DeployedEnvironments({deployed_environments: deployed});
    
        expect(dn).toBeNull();
    });
});