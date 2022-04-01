#!/usr/bin/python
import json, sys, StringIO

if __name__=='__main__':

	argv_io_string = StringIO.StringIO(sys.argv[1])
	json_variables = json.load(argv_io_string)

	output = {} 
	output['plotbar'] = {
                "data": [
                        {
                                "x": [
                                        "giraffes",
                                        "orangutans",
                                        "monkeys"
                                ],
                                "y": [
                                        20,
                                        14,
                                        23
                                ],
                                "type": "bar"
                        }
                ]
        }
        

        output['plotline'] = {
                "data" : [
                        {
                                "x": [1, 2, 3, 4],
                                "y": [10, 15, 13, 17],
                                "mode": "markers",
                                "marker": {
                                        "color": "rgb(219, 64, 82)",
                                        "size": 12
                                }
                        },
                        {
                                "x" : [2, 3, 4, 5],
                                "y" : [16, 5, 11, 9],
                                "mode" : "lines",
                                "line" : {
                                        "color" : "rgb(55, 128, 191)",
                                        "width": 3
                                }
                        },
                        {
                                "x" : [1, 2, 3, 4],
                                "y" : [12, 9, 15, 12],
                                "mode" : "lines+markers",
                                "marker" : {
                                        "color" : "rgb(128, 0, 128)",
                                        "size": 8
                                },
                                "line" : {
                                        "color" : "rgb(128, 0, 128)",
                                        "width" : 1
                                }
                        }
                ],
                "layout" : {
                        "title" : "Line and Scatter Styling"
                }
        }
        

        output['_textarea'] = "JSON output from executable:\n" + json.dumps( output, indent=4 ) + "\n\n";
        output['_textarea'] += "JSON input to executable:\n" + json.dumps( json_variables, indent=4 ) + "\n";

	print json.dumps(output)
		
