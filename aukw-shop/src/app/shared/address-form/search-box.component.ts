import {
  Component,
  Input,
  OnInit,
  Output,
  EventEmitter,
  ElementRef,
} from '@angular/core';

// By importing just the rxjs operators we need, We're theoretically able
// to reduce our build size vs. importing all of them.
import { fromEvent } from 'rxjs';
import { map, filter, debounceTime, tap, switchMap } from 'rxjs/operators';

import { postcodeRegex } from '../regexes.const';

import { AddressSearchService } from '@app/_services';
import { Address, GetAddressAddress } from '@app/_models';

@Component({
  selector: 'address-search-box',
  template: `
  <div class="input-group">
    <div class="input-group-prepend">
      <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
    </div>
    <input
      type="text"
      class="form-control"
      placeholder="Search By Postcode"
    />
  </div>
  `,
})
export class SearchBoxComponent implements OnInit {
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() results: EventEmitter<GetAddressAddress[]> = new EventEmitter<GetAddressAddress[]>();
  @Input() disable: boolean = false;

  constructor(
    private addressSearchService: AddressSearchService,
    private el: ElementRef
  ) {}

  // Create an observable from the stream of characters being entered in the search box
  // and convert those values into an array of MemberSearchResult objects

  // See https://rxjs-dev.firebaseapp.com/guide/v6/migration for pipe format.
  ngOnInit(): void {
    if (this.disable) {
      return;
    }

    const postcode2 = new RegExp(postcodeRegex, 'i');

    // convert the `keyup` event into an observable stream
    fromEvent(this.el.nativeElement, 'keyup')
      .pipe(
        map((e: any) => e.target.value), // extract the value of the input

        filter((text: string) => text.length > 3 && postcode2.test(text)), // filter out if invalid postcode

        debounceTime(250), // only once every 250ms

        tap((query: string) => this.loading.emit(true)), // enable loading

        // search, discarding old events if new input comes in
        switchMap((query: string) => this.addressSearchService.search(query))
      )
      // act on the return of the search
      .subscribe(
        (results: GetAddressAddress[]) => {
          // on sucesss
          this.loading.emit(false);
          this.results.emit(results);
        },
        (err: any) => {
          // on error
          console.log(err);
          this.loading.emit(false);
        },
        () => {
          // on completion
          this.loading.emit(false);
        }
      );
  }
}
