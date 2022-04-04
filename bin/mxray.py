#!/opt/miniconda3/bin/python

import json
import io
import sys
import os
import socket # for sending progress messages to textarea
from genapp3 import genapp
import numpy as np
import matplotlib.pyplot as plt
from matplotlib import gridspec
import subprocess
from helpfunctions import *
import time
from fast_histogram import histogram1d #histogram1d from fast_histogram is faster than np.histogram (https://pypi.org/project/fast-histogram/)
import gc # garbage collector for freeing memory
from sys import getsizeof
import time
import subprocess
import locale, multiprocessing

if __name__=='__main__':

   ################### IMPORT INPUT FROM GUI #####################################

    ## read global Json input (input from GUI)
    argv_io_string = io.StringIO(sys.argv[1])
    json_variables = json.load(argv_io_string)
    xi = float(json_variables['xi'])
    eta = float(json_variables['eta'])
    q = float(json_variables['q']) # number of points in (simulated) q
    qzb = float(json_variables['qzb'])
    qze = float(json_variables['qze']) # number of points in p(r)
    qzs = float(json_variables['qzs'])
    DataFile = json_variables['data']
    folder = json_variables['_base_directory'] # output folder dir


    UDP_IP = json_variables['_udphost']
    UDP_PORT = int( json_variables['_udpport'] )
    sock = socket.socket(socket.AF_INET, # Internet
            socket.SOCK_DGRAM) # UDP

    socket_dict={}
    socket_dict['_uuid'] = json_variables['_uuid']


    # message = genapp(json_variables)
    output = {} # create an empty python dictionary

    socket_dict['_textarea'] = "Process started ..."
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))

    s = subprocess.check_output(["mxray_bending","-m","fitd", "-z", str(xi),"-e" ,str(eta), "-f", str(DataFile[0]),"--Lr","300","--sr","100","-o","TestFit"])

    file1 = open('TestFit_fitted.fit', 'r')
    Lines = file1.readlines()

    tmp = Lines[10].strip()
    splittext = tmp.split('=')
    B = float(splittext[1])
    # print(B)
    tmp = Lines[11].strip()
    splittext = tmp.split('=')
    dB = float(splittext[1])
    # print(dB)
    tmp = Lines[12].strip()
    splittext = tmp.split('=')
    Kc = float(splittext[1])
    # print(Kc)
    tmp = Lines[13].strip()
    splittext = tmp.split('=')
    dKc = float(splittext[1])
    # print(dKc)
    file1.close()
    DummyData = np.genfromtxt('TestFit_fitted.fit', delimiter='\t', skip_header=31)
    Data_dict={}
    Data_dict['x'] = DummyData[:,0]
    Data_dict['y'] = DummyData[:,1]
    Data_dict['mode'] = "markers"
    Data_dict['marker'] = {
            "color": "rgb(219, 64, 82)",
            "size": 12
    }

    Fit_dict={}
    Fit_dict['x'] = DummyData[:,2]
    Fit_dict['y'] = DummyData[:,3]
    Fit_dict['mode'] = "lines"
    Fit_dict['line'] = {
            "color" : "rgb(200, 0, 0)",
            "width": 3
    }

    Datatmp = []
    Datatmp.appen(Data_dict)
    Datatmp.appen(Fit_dict)
    Graph_dict={}
    Graph_dict["data"] = Datatmp
    Graph_dict["layout"] = {
            "title" : "Fit Results"
    }

    output['plotline'] = json.dumps(Graph_dict)

    buff_Kc = "%.2f +- %.2f"%(Kc,dKc)
    buff_B = "%.2e +- %.2e" % (B, dB)
    output["kc"] = buff_Kc
    output["B"] = buff_B

    output['_textarea'] = s.decode("utf-8")
    output["Fit"] = "%s/TestFit_fitted.fit" % folder

    print( json.dumps(output) ) # convert dictionary to json and output
