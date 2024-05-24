import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { AuthenticationService, UserService } from '@app/_services';
import { User } from '@app/_models';
import { UserRowComponent } from './row.component';

@Component({
  templateUrl: 'list.component.html',
  styleUrl: './list.component.css',
  standalone: true,
  imports: [RouterLink, NgFor, NgIf, UserRowComponent],
})
export class UserListComponent implements OnInit {
  users!: User[];
  user!: User;

  constructor(
    private userService: UserService,
    private authenticationService: AuthenticationService,
    private route: ActivatedRoute,
    private router: Router,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.userService.getAll().subscribe((users) => {
      this.users = users;
      // Depending on route, set inital state
      if (this.router.url.substring(0, 16) === '/users/suspended') {
        this.users = this.users.filter(
          (x) =>
            x.suspended ==
            (this.route.snapshot.params['suspended'] === 'true' ? true : false),
        );
      }
    });
  }

  userWasDeleted(user: User): void {
    this.users = this.users.filter((x) => x.id !== user.id);
  }
}
