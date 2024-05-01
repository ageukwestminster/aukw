import { Routes } from '@angular/router';

import { TakingsListComponent } from './list/list.component'
import { TakingsAddEditComponent } from './add-edit/add-edit.component';

export const TAKINGS_ROUTES: Routes = [
    { path: '', component: TakingsListComponent },
    { path: 'add', component: TakingsAddEditComponent },
    { path: 'edit/:id', component: TakingsAddEditComponent },
    { path: 'view/:id', component: TakingsAddEditComponent },
    ];
