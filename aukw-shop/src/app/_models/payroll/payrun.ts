/**
 * information about a single Pay Run, including the relevant TaxYear, TaxMonth 
 * 
 * There is a PayRun for each period in which people are paid.
 */
export class PayRun {
  /** Display name of Pay Run */
  name: string;
  /** The number of the month of the Pay Run. The range is 1-12 but it follows the fiscal year 
   * so April is 1, May is 2 ..... March is 12 */
  taxMonth: number;
  /** The tax year associated with the Pay Run. For example 'Year2024', 'Year2025' etc. */
  taxYear: string;
  /** The date of the first day of the Pay Run. Usually the 1st of the month. */
  startDate: string;
  /** The date of the last day of the Pay Run. Usually the last of the month. */
  endDate: string;
  /** 'True' if the Pay Run is closed and finalized */
  isClosed: boolean = false;
  /** One of "Opening","Open","ReOpening","RollingBack","RolledBack",
   * "SubmittedForProcessing","Processing","AwaitingApproval","Approved",
   * "Finalising","Finalised","Deleting" */
  state: string;
  /** The total cost of employement sumeed over all employees */
  totalCost: number;
  /** The total of gross salaries. Excludes salary sacrifices. */
  gross: number;
  /** The total amount of NI the employer is paying */
  employerNi: number;
  /** The total amount of pension the employer is paying */
  employerPensionContribution: number;
  /** The number of employees listed in this Pay Run. */
  employeeCount: number;
  /**The version of this PayRun in case any supplementary pay runs have been created for the same period. */
  version: number;
  /** 'True' if this is the latest version of the PayRun. */
  isLatestVersion: boolean = false;

  /**Create a new PayRun */
  constructor(obj?: any) {
    this.taxMonth = (obj && obj.taxMonth) || null;
    this.name = (obj && obj.name) || null;
    this.taxYear = (obj && obj.taxYear) || null;
    this.startDate = (obj && obj.startDate) || null;
    this.endDate = (obj && obj.endDate) || null;
    this.isClosed = (obj && obj.isClosed) || false;
    this.state = (obj && obj.state) || null;
    this.totalCost = (obj && obj.totalCost) || null;
    this.gross = (obj && obj.gross) || null;
    this.employerNi = (obj && obj.employerNi) || null;
    this.employerPensionContribution = (obj && obj.employerPensionContribution) || null;
    this.employeeCount = (obj && obj.employeeCount) || null;
    this.version = (obj && obj.version) || null;
    this.isLatestVersion = (obj && obj.isLatestVersion) || false;
  }
}
