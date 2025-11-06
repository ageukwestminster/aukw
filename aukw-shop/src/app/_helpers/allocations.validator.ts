import {
  AbstractControl,
  FormArray,
  FormGroup,
  ValidationErrors,
  ValidatorFn,
} from '@angular/forms';

/**
 * Custom validator for adding new employee allocations.
 * Ensures that the sum of all 'percentage' fields in the specified FormArray equals 100.
 *
 * @param controlName Name of the FormArray to check
 * @returns
 */
export function PercentagesMustSumToPar(controlName: string): ValidatorFn {
  return (group: AbstractControl): ValidationErrors | null => {

    try{

      const form = <FormGroup>group;
      const formArray = <FormArray>form.controls[controlName];

      var percentage: number = 0;
      var percentageControl: AbstractControl | null = null;

      // Loop through all allocations and sum percentages
      for (let i = 0; i < formArray.length; i++) {
        const control = formArray.at(i);
        percentageControl = control.get('percentage');
        percentage += Number(percentageControl?.value) || 0;
      }

      // If not 100%, set error on the last of the percentage controls
      // It will be annoying if the errors appear on every control
      if (percentage !== 100) {
          if (percentageControl) {
            percentageControl.setErrors({ percentagesMustSumToPar: true });
          }
      } else {
        // Clear all errors on all controls, not just the last
        for (let i = 0; i < formArray.length; i++) {
          const control = formArray.at(i);
          const percentageControl = control.get('percentage');
          if (percentageControl) {
            percentageControl.setErrors(null);
          }
        }
      }

      return null;


    } catch (error) {
      console.error('Error in PercentagesMustSumToPar validator:', error);
      return null; // Don't stop execution flow due to validator error
    }
    
  };
}
