export class SalesChartData {
    date: string;   
    sales:  number;
    avg10:  number;
    avg30:  number;
    avg365:  number;
    avgAll:  number;    

    constructor(obj?: any) {

        this.date = obj && obj.date || null;
        this.sales = obj && obj.sales || null;
        this.avg10 = obj && obj.avg10 || null;
        this.avg30 = obj && obj.avg30 || null;
        this.avg365 = obj && obj.avg365 || null;
        this.avgAll = obj && obj.avgAll || null;
    }
}