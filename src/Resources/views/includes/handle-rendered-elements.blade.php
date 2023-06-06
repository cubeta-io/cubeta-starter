@php
    $title = '' ;
    $textUnderTitle = '' ;
    $action = '' ;
    $modelNameField = false ;
    $attributesField = false ;
    $relationsField = false ;
    $actorsField = false ;
    if (request()->fullUrl() == route('cubeta-starter.full-generate.page')){
        $action = route('cubeta-starter.call-create-model-command');
        $modelNameField = true ;
        $attributesField = true ;
        $relationsField = true ;
        $actorsField = true ;
        $title = 'Generate The CRUDs' ;
        $textUnderTitle = 'Here We Will Create Your Model And All The Others Needs To Have A Complete CRUD API' ;
    }

@endphp
