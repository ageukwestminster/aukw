export class QBConnectionDetails {  
    authUri: string | null; 
    refreshExpiry: string | null;

    constructor(obj?: any) {        
        this.refreshExpiry = obj && obj.refreshExpiry || null;
        this.authUri = obj && obj.authUri || null;
    }
}