import { Component, inject, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { toSignal } from '@angular/core/rxjs-interop';

import { AuthenticationService } from '@app/_services';
import { User } from '@app/_models';
import { UserRowComponent } from './row.component';

@Component({
  templateUrl: 'list.component.html',
  styleUrl: './list.component.css',
  standalone: true,
  imports: [RouterLink, UserRowComponent],
})
export class UserListComponent implements OnInit{
  users: User[] = [];
  user!: User;  

  private route = inject(ActivatedRoute);
  private authenticationService = inject(AuthenticationService);
  private location = inject(Location);

  private data = toSignal(this.route.data);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(){
    if (this.data()){
      this.users = this.data()!['users'] as User[]||[];
    }
  }

  userWasDeleted(user: User): void {
    this.users = this.users.filter((x) => x.id !== user.id);
  }

  /** Return to previous page */
  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
