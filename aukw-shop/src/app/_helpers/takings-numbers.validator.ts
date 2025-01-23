import { AbstractControl, FormGroup,  ValidationErrors, ValidatorFn } from '@angular/forms';

/**
 * Custom validator for the add/edit Takings UI. If there is a non-zero
 * value for a department's sales then there must be a non-zero number of
 * items too. 
 *
 * @param controlName Name of first control to check
 * @returns 
 */
export function MustProvideNumberOfItems(controlName: string): ValidatorFn {
  return (group: AbstractControl) : ValidationErrors | null => {
    const form = <FormGroup>group;

    const valueControl = form.controls[controlName];
    const numberOfItemsControl = form.controls[controlName+'_num'];

    if (valueControl.value && !numberOfItemsControl.value) {
      numberOfItemsControl.setErrors({ mustProvideNumberOfItems: true });
    } else {
      numberOfItemsControl.setErrors(null);
    }

    return null;
  };
}

