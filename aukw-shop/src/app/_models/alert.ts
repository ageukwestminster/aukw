/**
 * Define the properties of an Alert
 */
export class Alert {
  constructor(
    public id: string,
    public type?: AlertType,
    public message?: string,
    public autoClose: boolean = true,
    public keepAfterRouteChange: boolean = false,
    public fade: boolean = false,
  ) {}
}

/**
 * All possible types of {@link Alert}
 */
export enum AlertType {
  Success,
  Error,
  Info,
  Warning,
}
