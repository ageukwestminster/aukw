import { Component, forwardRef, Input } from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';

/*******
 * 
 * 
 * UNFINISHED
 * 
 * 
 * 
 * 
 * ****/


@Component({
  selector: 'money-input',
  templateUrl: './money-input.component.html',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => MoneyInputComponent),
      multi: true,
    },
  ],
})

/*
*  Extends input html component so that value is always displayed as a 2 digit number
*/
// From https://blog.woodies11.dev/how-to-properly-implement-controlvalueaccessor/
export class MoneyInputComponent implements ControlValueAccessor {
  private field: number =0;

  // Save the callbacks, make sure to have a default so your app
  // doesn't crash when one isn't (yet) registered
  private onChange = (v: any) => {};
  private onTouched = () => {};

  registerOnChange(fn: any): void {
    this.onChange = fn;
  }
  registerOnTouched(fn: any): void {
    this.onTouched = fn;
  }

  writeValue(obj: any): void {
    return;
  }

}