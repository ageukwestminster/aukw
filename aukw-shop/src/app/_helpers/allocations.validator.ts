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
export function ProjectAllocationsValidater(controlName: string): ValidatorFn {
  return (group: AbstractControl): ValidationErrors | null => {
    try {
      const form = <FormGroup>group;
      const formArray = <FormArray>form.controls[controlName];

      var percentage: number = 0;
      var percentageControl: AbstractControl | null = null;
      var projectNames: string[] = [];

      // Loop through all allocations and sum percentages
      for (let i = 0; i < formArray.length; i++) {
        const control = formArray.at(i);
        percentageControl = control.get('percentage');
        percentage += Number(percentageControl?.value) || 0;

        // If not 100% by the time we get to the last control then set error
        if (percentage !== 100 && i === formArray.length - 1) {
          percentageControl?.setErrors({ percentagesMustSumToPar: true });
        } else {
          // Clear all errors
          percentageControl?.setErrors(null);
        }

        const projectName = control.get('project')?.value ?? '';
        if (projectName != '') {
          if (projectNames.indexOf(projectName) === -1) {
            projectNames.push(projectName);
          } else {
            // Duplicate project name found
            const projectControl = control.get('project');
            if (projectControl) {
              projectControl.setErrors({ duplicateProject: true });
            }
          }
        } else if (i === formArray.length - 1) {
          if (!projectNames.length) {
            // All project names are empty
            const projectControl = control.get('project');
            if (projectControl) {
              projectControl.setErrors({ required: true });
            }
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
