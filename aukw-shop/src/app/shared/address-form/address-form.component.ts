/* Custom Form Control code taken from https://github.com/xiongemi/angular-form-ngxs/ */
import {
  Component,
  forwardRef,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import {
  ControlValueAccessor,
  FormBuilder,
  NG_VALUE_ACCESSOR, // Example: https://github.com/xiongemi/angular-form-ngxs/
  Validators,
} from '@angular/forms';
import { Subscription } from 'rxjs';

import { CountryService, GeocodeService } from '@app/_services';
import { Address, Country, GetAddressAddress } from '@app/_models';

@Component({
  selector: 'address-form',
  templateUrl: './address-form.component.html',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => AddressFormComponent),
      multi: true,
    },
  ],
})
export class AddressFormComponent
  implements ControlValueAccessor, OnInit, OnDestroy, OnChanges {
  @Input() touched: boolean = false;
  @Input() address?: Address;

  addresses!: GetAddressAddress[]; // from the api
  countries!: Country[];
  uk!: Country;
  loading: boolean = false; // set by 'address-search-box' component
  onChange: any = (_: Address) => {};
  onTouch: any = () => {};
  submitted: boolean = false;
  showFormFields: boolean = false;

  addressForm = this.fb.group({
    addressfirstline: [null, Validators.required],
    addresssecondline: [null],
    city: [null, Validators.required],
    county: [null],
    country: [null, Validators.required],
    postcode: [null, Validators.required],
    lat: [null],
    lng: [null],
  });

  private subscription = new Subscription();

  constructor(
    private fb: FormBuilder,
    private countryService: CountryService,
    private geocodeService: GeocodeService
  ) {}

  // convenience getter for easy access to form fields
  get f() {
    return this.addressForm.controls;
  }

  ngOnInit(): void {
    this.countryService.getAll().subscribe((countryArray) => {
      this.countries = countryArray;
      this.uk = countryArray.filter(
        (c: Country) => c.name === 'United Kingdom'
      )[0];
      if (this.address && this.address.addressfirstline) {
        this.addressForm.controls['addressfirstline'].setValue(
          this.address.addressfirstline
        );
        this.addressForm.controls['addresssecondline'].setValue(
          this.address.addresssecondline
        );
        this.addressForm.controls['city'].setValue(this.address.city);
        this.addressForm.controls['county'].setValue(this.address.county);
        this.addressForm.controls['country'].setValue(this.address.country);
        this.addressForm.controls['postcode'].setValue(this.address.postcode);
        this.addressForm.controls['lat'].setValue(this.address.lat);
        this.addressForm.controls['lng'].setValue(this.address.lng);
        this.showFormFields = true;
      } else {
        this.addressForm.controls['country'].setValue(this.uk.id);
      }
    });

    this.subscription.add(
      this.addressForm.valueChanges.subscribe((value: Address) => {
        this.onChange(value);
      })
    );
  }
  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  ngOnChanges(simpleChanges: SimpleChanges) {
    if (simpleChanges['touched'] && simpleChanges['touched'].currentValue) {
      this.addressForm.markAllAsTouched();
    }
  }

  writeValue(value: null | Address): void {
    if (value) {
      this.addressForm.reset(value);
    }
  }

  registerOnChange(fn: () => {}): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: (_: Address) => {}): void {
    this.onTouch = fn;
  }

  updateAddresses(results: GetAddressAddress[]): void {
    this.addresses = results;
  }

  onAddressChange(address: GetAddressAddress): void {
    this.showFormFields = true;
    this.addressForm.controls['addressfirstline'].setValue(address.line1);
    this.addressForm.controls['addresssecondline'].setValue(address.line2);
    this.addressForm.controls['city'].setValue(address.town);
    this.addressForm.controls['county'].setValue(address.county);
    this.addressForm.controls['country'].setValue(address.country.id);
    this.addressForm.controls['postcode'].setValue(address.postcode);

    let a = new Address(this.addressForm.value);
    this.geocodeService.geocode(a).subscribe((new_address:Address) => {
      if (new_address.lng && new_address.lat) {
        this.addressForm.controls['lat'].setValue(new_address.lat);
        this.addressForm.controls['lng'].setValue(new_address.lng);
      }
    } );
  }
}
