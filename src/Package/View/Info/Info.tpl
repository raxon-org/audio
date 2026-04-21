{{$request = request()}}
Package: {{$request.package}}
Module: {{$request.module|>string.uppercase.first}}
{{if(!is.empty($request.submodule))}}
Submodule: {{$request.submodule|>string.uppercase.first}}
{{/if}}

Application: Audio
