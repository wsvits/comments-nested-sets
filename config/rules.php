<?php

return [
    '' => 'comment/index',
    'GET comments/<id:\d+>' => 'comment/list',
    'POST comments/<id:[\d]+>' => 'comment/create',
    'PUT comments/<id:[\d]+>' => 'comment/update',
    'DELETE comments/<id:[\d]+>' => 'comment/delete',
];