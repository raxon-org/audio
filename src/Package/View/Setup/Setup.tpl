{{$register = Package.Raxon.Audio:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Audio:Import:role.system()}}
{{$flags = flags()}}
{{$options = options()}}
{{Package.Raxon.Audio:Main:install($flags, $options)}}
{{/if}}