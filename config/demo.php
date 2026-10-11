<?php
return [
    'accounts' => [
        'admin@careroute.test' => ['name'=>'Demo Admin','role'=>'admin','hospital'=>null],
        'hospital.c@careroute.test' => ['name'=>'Hospital C Operator','role'=>'hospital','hospital'=>'C'],
        'hospital.b@careroute.test' => ['name'=>'Hospital B Operator','role'=>'hospital','hospital'=>'B'],
        'patient@careroute.test' => ['name'=>'Demo Patient','role'=>'patient','hospital'=>null],
    ],
    'modules' => [
        'dashboard'=>['name'=>'Dashboard','group'=>'Care coordination','url'=>'emergency','roles'=>['admin','hospital','patient']],
        'request'=>['name'=>'Emergency Request','group'=>'Care coordination','url'=>'emergency#patientForm','roles'=>['admin','patient']],
        'queue'=>['name'=>'Hospital Approval Queue','group'=>'Care coordination','url'=>'emergency#queue','roles'=>['admin','hospital']],
        'beds'=>['name'=>'Bed Dashboard','group'=>'Care coordination','url'=>'emergency#hospitals','roles'=>['admin','hospital','patient']],
        'history'=>['name'=>'Reservation Status & Decision Log','group'=>'Care coordination','url'=>'emergency#events','roles'=>['admin','hospital','patient']],
        'Hospital'=>['name'=>'Hospital Master','group'=>'Masters','url'=>'Hospital','roles'=>['admin']],
        'Bedtype'=>['name'=>'Bed Type Master','group'=>'Masters','url'=>'Bedtype','roles'=>['admin']],
        'BloodType'=>['name'=>'Blood Type Master','group'=>'Masters','url'=>'BloodType','roles'=>['admin']],
        'BedInven'=>['name'=>'Bed Inventory','group'=>'Inventory','url'=>'BedInven','roles'=>['admin']],
        'BloodInven'=>['name'=>'Blood Inventory','group'=>'Inventory','url'=>'BloodInven','roles'=>['admin']],
        'modules'=>['name'=>'Modules & Role Assignments','group'=>'Administration','url'=>'demo/modules','roles'=>['admin']],
        'users'=>['name'=>'Demo Users & Login Guide','group'=>'Administration','url'=>'demo/users','roles'=>['admin']],
        'ModuleMaster'=>['name'=>'Module Catalogue','group'=>'Administration','url'=>'ModuleMaster','roles'=>['admin']],
        'ModuleAssigning'=>['name'=>'Module Assigning','group'=>'Administration','url'=>'ModuleAssigning','roles'=>['admin']],
        'NewUser'=>['name'=>'User Management','group'=>'Administration','url'=>'NewUser','roles'=>['admin']],
    ],
];
