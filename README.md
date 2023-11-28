# MEDUSA
Access MEDUSA at <medusa.genapp.rocks>

# How To
1. Reduction

To get started, go to the Reduction tab and select the image data you want to analyze. Only .tif and .tiff files are currently supported.

Next, choose the points of interest that will determine the slices to be analyzed. Input the corresponding Q_z parameters, separated by commas (ignoring spaces).

After that, provide the instrument parameters, such as the sample-to-detector distance (mm), detector pixel size (mm), origin in x (pixels), and specify whether the origin should be detected or read from provided values.

Before submitting on this tab, indicate if there is distortion in either dimension of Q space, so the necessary corrections can be applied.

Once everything is set, press submit and wait for all processes to complete. Afterward, you can download the outputs.zip file, which contains all the graphs and plots saved in .png format. Additionally, it includes a .pkl file of the X-ray object used, a .json file with data for displaying the Plotly figures, and a .dat file for the Fitting tab. Note that the zip file is not required for using the Fitting tab.

1. Fitting

Moving from the Reduction tab to the Fitting tab, not all parameters need to be changed. The user needs to input the start values for Xi (ξ, Angstroms) and Eta (η, arbitrary unit) as well as the d-spacing in q (Angstroms^(-1)), which represents the position of the first order Bragg peak. However, it is important to note that Domain Size Lr (Angstroms) and Domain Size spread si (Angstroms) are instrument-specific parameters. It is recommended to refer to our publication before making any changes to these values.

Once the correct parameters are entered, click submit. After the fitting process is completed, two values will be displayed: Bending Modulus and Interaction Modulus. Below them, there will be an additional outputs.zip download containing the previously mentioned contents, as well as the fitting plots. The Xi^2 convergence is shown at the top, while the fit results plot is displayed at the bottom.

That covers all the necessary information. Now, you can proceed back to the Reduction tab for analysis.


# MEDUSA is developed by:

Dr. Sebastian Himbert (Department of Physics and Astronomy, McMaster University, Hamilton, ON L8S 4M1, Canada)
Dorian Royce Cicerone Gaboo (Department of Physics and Astronomy, McMaster University, Hamilton, ON L8S 4M1, Canada)
Dr. Emre Brooks (Chemistry and Biochemistry, University of Montana, Missoula, MT, USA)
Dr. John Nagle (Department of Physics, Carnegie Mellon University, Pittsburgh, PA 15213, USA)
Dr. Maikel C. Rheinstädter (Department of Physics and Astronomy, McMaster University, Hamilton, ON L8S 4M1, Canada)

# Literature

1.S. Himbert, Biophysics of Blood Membranes, PhD Thesis, McMaster University .  <http://hdl.handle.net/11375/26995>

2.S. Himbert, A. D’Alessandro, S. M. Qadri, M. J. Majcher, T. Hoare, W. P. Sheffield, M. Nagao, J. F. Nagle, and M. C. Rheinstädter The Bending Rigidity of Red Blood Cell Membranes, PLOS ONE 17(8): e0269619. <https://doi.org/10.1371/journal.pone.0269619>

3. S. Himbert, A. D’Alessandro, S. M. Qadri,W. P. Sheffield, J. F. Nagle, and M. C. Rheinstädter. Storage of red blood cells leads to an increased membrane order and bending rigidity, PLOS ONE 16(11): e0259267. https://doi.org/10.1371/journal.pone.0259267

4. Y. Lyatskaya, L. Yufeng, S. Tristram-Nagle, J. Katsaras, and J. F. Nagle. Method for obtaining structure and interactions from oriented lipid bilayers., Physical Review E 63, no. 1 (2000): 011907)  https://doi.org/10.1103/PhysRevE.63.011907

# Contact

Dr. Sebastian Himbert
McMaster University
Department of Physics and Astronomy
1280 Main Street West
Hamilton, ON, L8S 3W5
Email:himberts@mcmaster.ca
Website:sebastianhimbert.com

# Disclaimer

MEDUSA is distributed "as is", without any warranty, including any implied warranty of merchantability or fitness for a particular use. The authors assume no responsibility for, and shall not be liable for, any special, indirect, or consequential damages, or any damages whatsoever, arising out of or in connection with the use of this software.
