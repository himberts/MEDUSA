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
from XrayLib import xray

if __name__=='__main__':

   ################### IMPORT INPUT FROM GUI #####################################

    ## read global Json input (input from GUI)
    argv_io_string = io.StringIO(sys.argv[1])
    json_variables = json.load(argv_io_string)
    qparcutstext = json_variables['qparcuts']
    SampleDetectorDistance = float(json_variables['sd'])
    PixelSize = float(json_variables['pxs']) # number of points in p(r)
    XOrigin = float(json_variables['xorigin'])
    YOrigin = float(json_variables['yorigin'])
    WaveLength = float(json_variables['lambda'])
    DataFile = json_variables['data']
    folder = json_variables['_base_directory'] # output folder dir

    # message = genapp(json_variables)
    output = {} # create an empty python dictionary
    UDP_IP = json_variables['_udphost']
    UDP_PORT = int( json_variables['_udpport'] )
    sock = socket.socket(socket.AF_INET, # Internet
            socket.SOCK_DGRAM) # UDP

    socket_dict={}
    socket_dict['_uuid'] = json_variables['_uuid']

    socket_dict['_textarea'] = "Process started ..."
    socket_dict['progressbar'] = 0
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))



    a = xray(filename=str(DataFile[0]), px=0.1, sampledetectdist=SampleDetectorDistance, xorigin=XOrigin, yorigin=YOrigin, wavelength=WaveLength, q1='0.0945')

    socket_dict['_textarea'] = "Data Loaded ..."
    socket_dict['progressbar'] = 0.2
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))

    qparCutsList  = qparcutstext.split(',')
    qparCuts = np.ndarray(len(qparCutsList))
    for k in range(len(qparCutsList)):
        qparCuts[k] = float(qparCutsList[k])
        # content = "%s \n %f" % (content,qparCuts[k])

    a.plottiff(show=0)
    socket_dict['_textarea'] = "2D Graphics created ..."
    socket_dict['progressbar'] = 0.4
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))
    a.cropimg(qparCuts, showcrop=1)
    # a.cropimg(point1=0.3, point2=0.35, plot=0)
    socket_dict['_textarea'] = "qr cuts created ..."
    socket_dict['progressbar'] = 0.6
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))

    a.calcmeanandplot(show=0, showopt=0)

    socket_dict['_textarea'] = "Cuts averaged ..."
    socket_dict['progressbar'] = 0.8
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))

    a.export()

    socket_dict['_textarea'] = "Data exported ..."
    socket_dict['progressbar'] = 1
    # socket_dict['progress_html'] = '<center>'+svalue+'</center>'
    doc_string = json.dumps(socket_dict)
    sock.sendto(doc_string.encode(),(UDP_IP,UDP_PORT))
    #
    #
    output["reddat"] = "%s/outputfile.dat" % folder
    output['_textarea'] =  '%s' % folder#"Reduction Completed; Please Continue on the Fitting tab"
    output['Data2D'] = '<img src="%s/Dataset.png" alt="2DGraphics">' % folder#"Reduction Complete ..."
    output['RedAreas'] = '<img src="%s/Dataset_allhighlights.png" alt="2DGraphics">' % folder#"Reduction Complete ..."
    print( json.dumps(output) ) # convert dictionary to json and output
