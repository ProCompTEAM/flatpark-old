﻿namespace DataCenter.Data.Dtos
{
    public class UserSettingsDto : IdentifiedDto
    {
        public string Name { get; set; }

        public string Attributes { get; set; }

        public string World { get; set; }

        public double X { get; set; }

        public double Y { get; set; }

        public double Z { get; set; }
    }
}
