/** interface for loading indicator */
export interface MessageFactories<T> {
  loading(): string;
  success(result: any): string;
  error(result: any): string;
}
