export class Summary {
    index: number;
    period: string;    
    type: string;
    start_date: string;
    end_date: string;
    net_sales: number;
    
    clothing: number;
    brica: number;
    books: number;
    linens: number;
    rag: number;
    other: number;
    donations: number;

    clothing_num: number;
    brica_num: number;
    books_num: number;
    linens_num: number;
    rag_num: number;
    other_num: number;
    donations_num: number;

    shopid: number;
    number_of_items_sold: number;
    customers_num_total: number;
    sales_total: number;
    expenses: number;
    cash_to_bank: number;
    credit_cards: number;

    constructor(obj?: any) {

        this.index = obj && obj.index || null;
        this.period = obj && obj.period || null;
        this.type = obj && obj.type || 0;
        this.start_date = obj && obj.start_date || 0;
        this.end_date = obj && obj.end_date || 0;
        this.shopid = obj && obj.shopid || null;
        this.number_of_items_sold = obj && obj.number_of_items_sold || null;
        this.customers_num_total = obj && obj.customers_num_total || null;
        this.sales_total = obj && obj.sales_total || null;
        this.rag = obj && obj.rag || null;
        this.net_sales = obj && obj.net_sales || null;
        this.expenses = obj && obj.expenses || null;
        
        this.clothing = obj && obj.clothing || null;        
        this.brica = obj && obj.brica || null;
        this.books = obj && obj.books || null;
        this.linens = obj && obj.linens || null;
        this.rag = obj && obj.rag || 0;
        this.other = obj && obj.other || 0;
        this.donations = obj && obj.donations || 0;

        this.clothing_num = obj && obj.clothing_num || null;
        this.brica_num = obj && obj.brica_num || null;
        this.books_num = obj && obj.books_num || null;
        this.linens_num = obj && obj.linens_num || null;
        this.rag_num = obj && obj.rag_num || 0;
        this.other_num = obj && obj.other_num || 0;
        this.donations_num = obj && obj.donations_num || 0;

        this.cash_to_bank = obj && obj.cash_to_bank || 0;
        this.credit_cards = obj && obj.credit_cards || 0;

    }
}