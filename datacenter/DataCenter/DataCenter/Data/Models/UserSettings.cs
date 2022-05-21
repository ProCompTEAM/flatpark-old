using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;

using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class UserSettings : BaseEntity, IUnited
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Name { get; set; }

        [Unicode(Defaults.DefaultStringLength)]
        public string Licenses { get; set; }

        [Unicode(Defaults.DefaultStringLength)]
        public string Attributes { get; set; }

        [Required]
        public int Organisation { get; set; }

        [Unicode(Defaults.DefaultStringLength)]
        public string World { get; set; }

        [Required]
        public double X { get; set; }

        [Required]
        public double Y { get; set; }

        [Required]
        public double Z { get; set; }
    }
}