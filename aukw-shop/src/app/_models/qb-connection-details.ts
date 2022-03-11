export class QBConnectionDetails {  
    refreshExpiry: string | null; 

    constructor(obj?: any) {        
        this.refreshExpiry = obj && obj.refreshExpiry || null;
    }
}