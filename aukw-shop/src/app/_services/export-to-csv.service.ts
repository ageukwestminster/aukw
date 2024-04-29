import { Injectable } from '@angular/core';
import csvDownload from 'json-to-csv-export';

@Injectable({ providedIn: 'root' })
export class ExportToCsvService {
  constructor() {}

  /**
   * Export the given data object to a CSV file
   *
   * @param data_to_export - The object to export. Must be an array
   *
   */
  exportToCSV(data_to_export: any): void {
    if (!data_to_export[0] || data_to_export[0].length === 0) {
      return;
    }

    //From https://www.npmjs.com/package/json-to-csv-export
    const dataToConvert = {
      data: data_to_export,
      filename: 'export.csv',
      delimiter: ',',
    };

    csvDownload(dataToConvert);
  }
}
