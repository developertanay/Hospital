<?php
return [
    'modules' => [
        'dashboard'=>['name'=>'Dashboard','group'=>'Care coordination','url'=>'emergency','roles'=>['admin','hospital','patient']],
        'request'=>['name'=>'Emergency Requests','group'=>'Care coordination','url'=>'emergency/requests','roles'=>['patient']],
        'queue'=>['name'=>'Hospital Approvals','group'=>'Care coordination','url'=>'emergency/approvals','roles'=>['hospital']],
        'beds'=>['name'=>'Bed Availability','group'=>'Care coordination','url'=>'emergency/beds','roles'=>['hospital']],
        'hospitalBeds'=>['name'=>'Bed Inventory','group'=>'Hospital management','url'=>'emergency/inventory','roles'=>['hospital']],
        'hospitalBlood'=>['name'=>'Blood Inventory','group'=>'Hospital management','url'=>'emergency/blood','roles'=>['hospital']],
        'hospitalSettings'=>['name'=>'Hospital Settings','group'=>'Hospital management','url'=>'emergency/settings','roles'=>['hospital']],
        'status'=>['name'=>'Reservation Status','group'=>'Care coordination','url'=>'emergency/reservations','roles'=>['patient']],
        'history'=>['name'=>'Decision History','group'=>'Care coordination','url'=>'emergency/history','roles'=>['hospital','patient']],
        'Hospital'=>['name'=>'Hospital Master','group'=>'Masters','url'=>'Hospital','roles'=>['admin']],
        'Bedtype'=>['name'=>'Bed Type Master','group'=>'Masters','url'=>'Bedtype','roles'=>['admin']],
        'BloodType'=>['name'=>'Blood Type Master','group'=>'Masters','url'=>'BloodType','roles'=>['admin']],
        'BedInven'=>['name'=>'Bed Inventory','group'=>'Inventory','url'=>'BedInven','roles'=>['admin']],
        'BloodInven'=>['name'=>'Blood Inventory','group'=>'Inventory','url'=>'BloodInven','roles'=>['admin']],
        'modules'=>['name'=>'Modules & Role Access','group'=>'Administration','url'=>'demo/modules','roles'=>['admin']],
        'users'=>['name'=>'Accounts & Hospital Access','group'=>'Administration','url'=>'Accounts','roles'=>['admin']],
        'ModuleMaster'=>['name'=>'Module Catalogue','group'=>'Administration','url'=>'ModuleMaster','roles'=>['admin']],
        'ModuleAssigning'=>['name'=>'Module Assigning','group'=>'Administration','url'=>'ModuleAssigning','roles'=>['admin']],
        'NewUser'=>['name'=>'User Management','group'=>'Administration','url'=>'NewUser','roles'=>['admin']],
    ],
];
