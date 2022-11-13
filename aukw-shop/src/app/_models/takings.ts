import { Shop } from '@app/_models';

export class Takings {
  id: number;
  date: string;
  shopid: Shop;
  clothing_num: number;
  brica_num: number;
  books_num: number;
  linens_num: number;
  donations_num: number;
  other_num: number;
  rag_num: number;
  clothing: number;
  brica: number;
  books: number;
  linens: number;
  donations: number;
  other: number;
  rag: number;
  customers_num_total: number;
  cash_to_bank: number;
  credit_cards: number;
  operating_expenses: number;
  volunteer_expenses: number;
  other_adjustments: number;
  cash_to_charity: number;
  cash_difference: number;
  comments: string;
  rags_paid_in_cash: Boolean;
  quickbooks: Boolean;

  isDeleting: boolean = false;
  isUpdating: boolean = false;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.date = (obj && obj.date) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.clothing_num = (obj && obj.clothing_num) || null;
    this.brica_num = (obj && obj.brica_num) || null;
    this.books_num = (obj && obj.books_num) || null;
    this.linens_num = (obj && obj.linens_num) || null;
    this.donations_num = (obj && obj.donations_num) || null;
    this.other_num = (obj && obj.other_num) || null;
    this.rag_num = (obj && obj.rag_num) || null;
    this.clothing = (obj && obj.clothing) || null;
    this.brica = (obj && obj.brica) || null;
    this.books = (obj && obj.books) || null;
    this.linens = (obj && obj.linens) || null;
    this.donations = (obj && obj.donations) || null;
    this.other = (obj && obj.other) || null;
    this.rag = (obj && obj.rag) || null;
    this.customers_num_total = (obj && obj.customers_num_total) || null;
    this.cash_to_bank = (obj && obj.cash_to_bank) || null;
    this.credit_cards = (obj && obj.credit_cards) || null;
    this.operating_expenses = (obj && obj.operating_expenses) || null;
    this.volunteer_expenses = (obj && obj.volunteer_expenses) || null;
    this.other_adjustments = (obj && obj.other_adjustments) || null;
    this.cash_to_charity = (obj && obj.cash_to_charity) || null;
    this.cash_difference = (obj && obj.cash_difference) || null;
    this.comments = (obj && obj.comments) || null;
    this.rags_paid_in_cash = (obj && obj.rags_paid_in_cash) || null;
    this.quickbooks = (obj && obj.quickbooks) || null;
  }
}
