// This file can be replaced during build by using the `fileReplacements` array.
// `ng build` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.

export const environment = {
  production: false,
  apiUrl: 'http://localhost/api',
  HARROWROAD_SHOPID: 1,
  loginUrl: 'http://localhost:4200/',
  qboEnterprisesRealmID: '9130350604308576', // Age UK Enterprises
  qboCharityRealmID: '123145825016867', // Age UK Westminster
  qboEnterprisesIntercompanyAccount: 80, // Enterporises interco account number
  qboCharityIntercompanyAccount: 65, // Charity inter account number
};

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/plugins/zone-error';  // Included with Angular CLI.
