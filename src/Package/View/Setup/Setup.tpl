{{$register = Package.Raxon.Ollama.Gui:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Ollama.Gui:Import:role.system()}}
{{$flags = flags()}}
{{$options = options()}}
{{Package.Raxon.Ollama.Gui:Main:install($flags, $options)}}
{{/if}}