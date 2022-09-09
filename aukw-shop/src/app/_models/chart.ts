export class Chart {
    name: string;   
    series:  any;
    

    constructor(obj?: any) {

        this.name = obj && obj.name || null;
        this.series = obj && obj.series || null;        

    }
}