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
from pathlib import Path
import shutil
import pickle
import glob

class GenappCom:
    def __init__(self):
        """

        __init__ method for GenappCom.No Parameter needed

        """
        self.UDP_IP = json_variables['_udphost']
        self.UDP_PORT = int( json_variables['_udpport'] )
        self.sock = socket.socket(socket.AF_INET, # Internet
                socket.SOCK_DGRAM) # UDP

        self.socket_dict={}
        self.socket_dict['_uuid'] = json_variables['_uuid']

    def postupdate(self,UpdateText,Progress):
        """
        postupdate method for GenappCom. Parameters that are passed through the class are determined by user/machine
        :param UpdateText: Text to be posted to the Genapp default messaging textbox
        :param Progress: Values between 0 and 1 indicating the progress of the reduction program

        """
        self.socket_dict['_textarea'] = UpdateText
        self.socket_dict['progressbar'] = Progress
        doc_string = json.dumps(self.socket_dict)
        self.sock.sendto(doc_string.encode(),(self.UDP_IP,self.UDP_PORT))
    def postcontent(self):
        content = ''
        for file in os.listdir(folder):
            content = "%s \n %s" % (content,file)

        self.socket_dict['_textarea'] = content
        doc_string = json.dumps(self.socket_dict)
        self.sock.sendto(doc_string.encode(),(self.UDP_IP,self.UDP_PORT))
    def postSubmittedKeys(self,JsonDict):
        content = ''
        for file in list(JsonDict.keys()):
            content = "%s \n %s" % (content,file)

        self.socket_dict['_textarea'] = content
        doc_string = json.dumps(self.socket_dict)
        self.sock.sendto(doc_string.encode(),(self.UDP_IP,self.UDP_PORT))


def ParseQcuts(qparcutstext,AvailableQPar):
    qparCutsList  = qparcutstext.split(',')
    qparCuts = np.ndarray(1)

    for idx, qparCut in enumerate(qparCutsList):
        print(qparCut)

        if '-' in qparCut:
            print('error')
            Bounds = qparCut.split('-')
            LowBoundVec   = np.argmin(np.abs(AvailableQPar - float(Bounds[0])))
            UpperBoundVec = np.argmin(np.abs(AvailableQPar - float(Bounds[1])))
            print(LowBoundVec)
            print(UpperBoundVec)
            ToAddList = AvailableQPar[LowBoundVec:UpperBoundVec+1]
            for ListItem in ToAddList:
                qparCuts = np.append(qparCuts, [ListItem])
        else:
            qparCuts = np.append(qparCuts, np.asarray([float(qparCut)]))

    qparCuts = np.delete(qparCuts,0)
    return qparCuts


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

    if 'distqpar' in json_variables:
        DistChi=1
    else:
        DistChi=0

    if 'distqz' in json_variables:
        DistTheta=1
    else:
        DistTheta=0

    output = {} # create an empty python dictionary
    GenappPost = GenappCom()
    GenappPost.postSubmittedKeys(json_variables)
    GenappPost.postupdate("Process started ... \n",0)

    rmfolder = '%s/outputs' % folder

    if os.path.exists(rmfolder) and os.path.isdir(rmfolder):
        shutil.rmtree(rmfolder)

    a = xray(filename=str(DataFile[0]), px=0.1, xorigin=XOrigin, yorigin=YOrigin, sampledetectdist=SampleDetectorDistance, wavelength=WaveLength, q1='0.0945',distTheta=DistTheta,distChi=DistChi)
    GenappPost = GenappCom()
    GenappPost.postupdate("Data Loaded ...\n",0.1)

    qparCuts = ParseQcuts(qparcutstext,a.qz)

    # qparCutsList  = qparcutstext.split(',')
    # qparCuts = np.ndarray(len(qparCutsList))
    # for k in range(len(qparCutsList)):
    #     qparCuts[k] = float(qparCutsList[k])

    a.plottiff(show=0)
    GenappPost = GenappCom()
    GenappPost.postupdate("2D Graphics created ...\n",0.2)

    a.cropimg(qparCuts, showcrop=1)
    GenappPost = GenappCom()
    GenappPost.postupdate("qr cuts created ...\n",0.3)

    a.calcmeanandplot(show=0, showopt=0)
    GenappPost = GenappCom()
    GenappPost.postupdate("Cuts averaged ...\n",0.4)

    a.plotreflectivity(showreflect=0)
    GenappPost = GenappCom()
    GenappPost.postupdate("Reflectivity Created ...\n",0.5)

    a.datadictjsons()
    a.export()
    GenappPost = GenappCom()
    GenappPost.postupdate("Data Exported ...\n",0.6)

    a.pickleme()
    GenappPost = GenappCom()
    GenappPost.postupdate("Reduction Status Saved...\n",0.7)
    #
    src = '/opt/genapp/mxray/add/XrayLib.py'
    dst = '%s/outputs/XrayLib.py' % (folder)

    shutil.copyfile(src, dst)

    src = '/opt/genapp/mxray/add/AnalysisTools.ipynb'
    dst = '%s/outputs/AnalysisTools.ipynb' % (folder)

    shutil.copyfile(src, dst)
    shutil.make_archive("outputs", "zip", os.getcwd() + "/outputs/")

    output["reddat"] = "%s/outputs.zip" % folder
    output['_textarea'] =  "Reduction Completed; Please Continue on the Fitting tab"
    output['progressbar'] =  1
    #
    output['Data2DPlotly'] = a.DataPlot2D
    output['ReflectivityPlotly'] = a.ReflectivityPlot
    output['DiffusePlotly'] = a.DiffusePlot
    GenappPost = GenappCom()
    GenappPost.postupdate("Sending Data ...\n",0.8)

    print( json.dumps(output) ) # convert dictionary to json and output
