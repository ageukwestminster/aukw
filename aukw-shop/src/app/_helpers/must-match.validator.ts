import { AbstractControl, FormGroup } from '@angular/forms';

/**
 * Custom validator to check that two control fields match
 *
 * @param controlName Name of first control to check
 * @param matchingControlName Name of second control to check
 * @returns If no match then return null, otherwise validate controls and exit
 */
export function MustMatch(controlName: string, matchingControlName: string) {
  return (group: AbstractControl) => {
    const formGroup = <FormGroup>group;
    const control = formGroup.controls[controlName];
    const matchingControl = formGroup.controls[matchingControlName];

    if (matchingControl.errors && !matchingControl.errors.mustMatch) {
      // return if another validator has already found an error on the matchingControl
      return null;
    }

    // set error on matchingControl if validation fails
    if (control.value !== matchingControl.value) {
      matchingControl.setErrors({ mustMatch: true });
    } else {
      matchingControl.setErrors(null);
    }

    return null;
  };
}
