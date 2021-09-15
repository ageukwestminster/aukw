import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { UserLayoutComponent } from './layout.component';
import { UserListComponent } from './list.component';
import { UserAddEditComponent } from './add-edit.component';

const routes: Routes = [
    {
        path: '', component: UserLayoutComponent,
        children: [
            { path: '', component: UserListComponent },
            { path: 'add', component: UserAddEditComponent },
            { path: 'edit/:id', component: UserAddEditComponent },
            { path: 'suspended/:suspended', component: UserListComponent }
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class UsersRoutingModule { }