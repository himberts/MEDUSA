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
    # qparcutstext = json_variables['qparcuts']
    SampleDetectorDistance = float(json_variables['sd'])
    PixelSize = float(json_variables['pxs']) # number of points in p(r)
    XOrigin = float(json_variables['xorigin'])
    YOrigin = float(json_variables['yorigin'])
    WaveLength = float(json_variables['lambda'])
    DataFile = json_variables['data']
    folder = json_variables['_base_directory'] # output folder dir

    # message = genapp(json_variables)
    output = {} # create an empty python dictionary

    a = xray(filename=str(DataFile[0]), px=0.1, sampledetectdist=SampleDetectorDistance, xorigin=XOrigin, yorigin=YOrigin, wavelength=WaveLength, q1='0.0945')
    # qparcuts = np.char.split(qparcutstext, ',').astype(np.float32)
    #a = xray(filename=str(DataFile[0]), px=PixelSize, sampledetectdist=332.1269, xorigin=410.983, yorigin=0.5101, wavelength=1.541867)
    # a.plottiff()
    a.plottiff(show=0)
    a.cropimg([0.1, 0.15, 0.2, 0.25, 0.3], showcrop=1)
    # a.cropimg(point1=0.3, point2=0.35, plot=0)
    a.calcmeanandplot(show=0, showopt=0)
    a.export()
    #
    #
    content = ''
    for file in os.listdir(folder):
        content = "%s \n %s" % (content,file)
    #
    output['_textarea'] =  content#"Reduction Complete ..."
    output['ImageOutput'] =  '<img src="Dataset.png" alt="2DGraphics">'#"Reduction Complete ..."

    print( json.dumps(output) ) # convert dictionary to json and output
