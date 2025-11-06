/**
 * An object that identifies a tax year.
 * The tax year is the fiscal year that runs from 6th April to 5th April the following year.
 */
export class TaxYear {
  /** The value displayed in, for example a dropdown, for this tax year  */
  name: string;
  /** The Staffology api value of each tax year. For example the 2024/25 tax year is called 'Year2024' */
  value: string;
  /** The starting year of the tax year. For example, for the 2024/25 tax year, this value is 2024 */
  year: number;

  /**Create a new TaxYear */
  constructor(obj?: any) {
    this.name = (obj && obj.name) || null;
    this.value = (obj && obj.value) || null;
    this.year = (obj && obj.year) || null;
  }
}
