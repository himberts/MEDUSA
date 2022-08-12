from PIL import Image
import matplotlib.pyplot as plt
import numpy as np
import math as m


class xray:
    def __init__(self, filename, px, sampledetectdist, xorigin, yorigin, wavelength):
        # TODO: (X-Chi-Q Parallel) and (Y_Theta-Q Z)
        """
        __init__ method for xray class. Parameters that are passed through the class are determined by user/machine

        :param filename: Name of the .tiff file that will be run through the class
        :param px: Conversion factor of pixle to millimetres
        :param sampledetectdist: distance between the sample and detector in millimetres
        :param xorigin: the x coordinate of the emitter with respect to the detector in pixels
        :param yorigin: the y coordinate of the emitter with respect to the detector in pixels
        :param wavelength: xray wavelength in Angstroms
        """
        self.px = px
        self.sampledetectdist = sampledetectdist
        self.xorigin = xorigin
        self.yorigin = yorigin
        self.name = filename
        self.wavelength = wavelength

        # Image related variables used for loading and manipulation
        self.img = None
        self.imgarr = None
        self.croppedimgarr = None
        self.xdim = 0
        self.ydim = 0
        self.xstart = 0
        self.xend = 0
        self.ystart = 0
        self.yend = 0

        # Objects for holding pixel measurements
        self.pxarr = None
        self.pxarr2 = None
        self.xpxarr = None
        self.ypxarr = None

        # Objects for holding metric measurements
        self.xarr = None
        self.yarr = None

        # Objects for holding angular measurements
        self.chiarr = None
        self.thetaarr = None

        # Variables for holding q space measurements, average and standard deviation
        self.qspacearr = None
        self.qparr = None
        self.qz = None
        # self.avgalongqp = None
        # self.avgalongqz = None
        self.qpavgstd = None
        self.qzavgstd = None

        # Parameters for .img
        self.headerbytes = 0
        self.sizey = 0
        self.sizex = 0
        self.wavelengthimg = 0.0
        self.amperage = 0.0
        self.voltage = 0.0
        self.energy = 0.0
        self.distortioninfo = None
        self.pixelsize = None
        self.detectorgonioset = None
        self.data = None

        # Variables for holding data for plotting the mean event counts
        self.meandata1 = None
        self.meandata2 = None

        # Variables for cropping data
        self.point1 = 0
        self.point2 = 0

        # Variables for data export into a .dat file
        self.error1 = None
        self.error2 = None
        self.qparrcrop = None
        self.md1crop = None
        self.md2crop = None
        self.e1crop = None
        self.e2crop = None
        self.frgrndcrop1 = None
        self.frgrndcrop2 = None

        # Methods for populating various arrays to be used later
        self.tifftoarr()
        self.pop_pxarrs()
        self.pop_metricarrs()
        self.pop_anglearrs()
        self.pop_qspacearrs()

    @staticmethod
    def getindex(arr, point):
        index = int(np.where(np.abs(arr - point) == np.min(np.abs(arr - point)))[0])
        return index

    @staticmethod
    def plot(xarr, yarr, colour, mini, maxi):
        plt.plot(xarr, yarr, color=colour)
        plt.ylim(mini, maxi)
        plt.show()

    @staticmethod
    def tonparray(num):
        """
        Converts a given string with multiple numbers spaced out with spaces and converts it into a numpy array with each number a float entry
        :param num: A string with multiple numbers in it
        :return: num - A numpy array
        """
        num = str.split(num)
        num = list(map(float, num))
        num = np.array(num)
        return num

    def tifftoarr(self):
        """
        Converts the given .tiff file to a NumPy array as well as defining some dimensional variables to streamline certain lines of code

        :return: None
        """
        self.img = Image.open(self.name)
        # noinspection PyTypeChecker
        self.imgarr = np.array(self.img)
        self.xdim = self.imgarr.shape[1]
        self.ydim = self.imgarr.shape[0]
        self.img.close()

    def plottiff(self):
        """
        Plots the provided .tiff file with pixels on axis

        :return: None
        """
        # print(self.xpxarr)
        # print(self.ypxarr)
        # print("\n")
        # print(self.xarr)
        # print(self.yarr)
        # print("\n")
        # print(self.chiarr)
        # print(self.thetaarr)
        # print("\n")
        # print(self.qparr)
        # print(self.qz)
        # print("\n")

        e = [self.qparr[0], self.qparr[-1], self.qz[-1], self.qz[0]]
        plt.figure()
        plt.imshow(np.log(self.imgarr + 1), extent=e)
        plt.xlabel("Q_||")
        plt.ylabel("Q_z")
        plt.show()

    def pop_pxarrs(self):
        """
        Populates the pixel arrays, xpxarr and ypxarr, with measurements in pixels

        :return: None
        """
        self.pxarr = np.indices(self.imgarr.shape)
        self.xpxarr = np.linspace(0, self.xdim, self.xdim, False)
        self.ypxarr = np.flip(np.linspace(0, self.ydim, self.ydim, False))

    def pop_metricarrs(self):
        """
        Populates the metric mesurement arrays, xarr and yarr, with measurements in millimetres

        :return: None
        """
        self.yarr = (self.ypxarr - self.yorigin) * self.px
        self.xarr = (self.xpxarr - (self.xdim - self.xorigin)) * self.px

    def pop_anglearrs(self):
        """
        Populates the angular space arrays, thetarr and chiarr, with measurements in degrees

        :return: None
        """
        rad2deg = 360 / (2 * m.pi)
        self.thetaarr = (self.yarr / self.sampledetectdist) * rad2deg
        self.chiarr = np.arctan(self.xarr / self.sampledetectdist) * rad2deg

    def pop_qspacearrs(self):
        """
        Populates the q space arrays, qz and qparr

        :return: None
        """
        deg2rad = 2 * m.pi / 360
        self.qparr = (4 * m.pi * np.sin((self.chiarr / 2) * deg2rad)) / self.wavelength
        self.qz = (4 * m.pi * np.sin(self.thetaarr / 2 * deg2rad)) / self.wavelength

    def calcmeanandplot(self, plot):
        """
        Averages the values of the image over the qparr axis, subtracts the background events and plots it

        :param plot: If true, displays the line graph plots of mean events vs qparr. If false, does nothing
        :return: None
        """

        self.meandata1 = np.mean(self.croppedimg1, 0)
        self.meandata2 = np.mean(self.croppedimg2, 0)

        index2_1 = self.getindex(self.qparr, -0.2)
        index2_2 = self.getindex(self.qparr, 0.2)
        index2_3 = self.getindex(self.qparr, -0.4)

        y1 = self.meandata1[index2_1]
        y2 = self.meandata1[index2_2]
        y3 = self.meandata1[index2_3]
        c = (y2 - y1) / (index2_2 - index2_1)
        b = y3 - c * index2_3

        y = lambda x: c * x + b

        background = y(self.qparr)
        meancrop1 = np.mean(self.croppedimg1, 0)
        meancrop2 = np.mean(self.croppedimg2, 0)
        self.frgrndcrop1 = np.subtract(meancrop1, background)
        self.frgrndcrop2 = np.subtract(meancrop2, background)

        # if plot:
        #     self.plot(self.qparr, self.meandata1, "tab:blue", 0, 100)
        #     self.plot(self.qparr, self.meandata2, "tab:orange", 0, 100)
        #     self.plot(self.qparr, self.frgrndcrop1, "tab:blue", 0, 100)
        #     self.plot(self.qparr, self.frgrndcrop2, "tab:orange", 0, 20)

    def loadimg(self, filename):
        fid = open(filename, 'r')
        fid.readline()
        temp = fid.readline()
        self.headerbytes = int(temp[temp.find("=") + 1:len(temp) - 2])

        for i in range(2):  # Skips 2 Lines
            fid.readline()

        temp = fid.readline()
        self.sizey = int(temp[temp.find("=") + 1:len(temp) - 2])
        temp = fid.readline()
        self.sizex = int(temp[temp.find("=") + 1:len(temp) - 2])

        for i in range(10):  # Skips 10 Lines
            fid.readline()

        temp = fid.readline()
        self.wavelengthimg = float(temp[temp.find(" "):len(temp) - 2])

        temp = fid.readline()
        self.amperage = float(temp[temp.find("=") + 1:len(temp) - 5])

        temp = fid.readline()
        self.voltage = float(temp[temp.find("=") + 1:len(temp) - 5])
        self.energy = self.amperage * self.voltage

        for i in range(21):  # Skips 21 lines
            fid.readline()

        temp = fid.readline()
        # Entries: [xorigin, yorigin, xpx size, ypx size]
        self.distortioninfo = temp[temp.find("=") + 1:len(temp) - 2]
        self.distortioninfo = self.tonparray(self.distortioninfo)
        self.pixelsize = self.distortioninfo[2:]

        for i in range(10):  # Skips 10 lines
            fid.readline()

        temp = fid.readline()
        self.detectorgonioset = temp[temp.find("=") + 1:len(temp) - 2]
        # Entries: [ x pos in deg, y pos in deg, z pos in deg, x pos in mm, y pos in mm, z pos in mm (detector sample distance)]
        self.detectorgonioset = self.tonparray(self.detectorgonioset)

        for i in range(48):  # Skips 48 lines
            fid.readline()

        fid.close()

        fid = open(filename, 'rb')

        fid.seek(self.headerbytes, 0)
        self.data = np.fromfile(fid, np.int32).reshape((self.sizex, self.sizey))
        print(self.data)
        fid.close()

        plt.imshow(np.log(self.data))
        plt.show()

    def cropimg(self, point1, point2, plot):
        """
        Crops the given image/colour map based on points provided by the user in a +/-10 px range

        :param point1: First point of interest
        :param point2: Second point of interest
        :param plot: Boolean, if true plots the cropped slices. If false, does nothing
        :return: None
        """
        self.point1 = point1
        self.point2 = point2

        self.croppedimg1 = self.imgarr[self.getindex(self.qz, point1) - 4:self.getindex(self.qz, point1) + 4, :]
        self.croppedimg2 = self.imgarr[self.getindex(self.qz, point2) - 4:self.getindex(self.qz, point2) + 4, :]

        # if plot:
        #     plt.figure()
        #     plt.imshow(self.croppedimg1)
        #     plt.xlabel("Q_||")
        #     plt.ylabel("Q_z")
        #
        #     # plt.figure()
        #     # plt.plot(self.qparr, np.mean(self.croppedimg1, 0))
        #
        #     plt.figure()
        #     plt.imshow(self.croppedimg2)
        #     plt.xlabel("Q_||")
        #     plt.ylabel("Q_z")
        #     plt.show()

    def export(self):
        file = "outputfile.dat"
        fh = open(file, "w")

        index1_1 = self.getindex(self.qparr, 0.01)
        index1_2 = self.getindex(self.qparr, 0.099)

        # self.error1 = np.sqrt(self.frgrndcrop1)
        # self.error2 = np.sqrt(self.frgrndcrop2)
        self.error1 = np.ones_like(self.frgrndcrop1)
        self.error2 = np.ones_like(self.frgrndcrop2)

        self.qparrcrop = self.qparr[index1_1:index1_2]
        self.md1crop = self.frgrndcrop1[index1_1:index1_2]
        self.e1crop = self.error1[index1_1:index1_2]
        self.md2crop = self.frgrndcrop2[index1_1:index1_2]
        self.e2crop = self.error2[index1_1:index1_2]

        data = np.column_stack((self.qparrcrop, self.md1crop, self.e1crop, self.md2crop, self.e2crop))
        # TODO: Figure out how error is calculated
        fmt = '%4.4f'
        header = ("{"
                  "\nNUMLINES=8" +
                  "\nFILENAME=" + self.name +
                  "\nNUMDATLINES=" + str(len(self.qparrcrop)) +
                  "\nQ1=" + str(0.0945) +
                  "\nQZSTART=" + str(self.point1) +
                  "\nQZSTART=" + str(self.point2) +
                  "\n}")

        np.savetxt(file, data, fmt=fmt, header=header, comments='')

        fh.close()
